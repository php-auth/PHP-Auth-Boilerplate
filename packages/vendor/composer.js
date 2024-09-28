#!/usr/bin/env node
/*
| ------------------------------------------------------------------------------
| Composer: https://getcomposer.org
|
| Download php dependencies.
| ------------------------------------------------------------------------------
*/
const argv    = require('minimist')(process.argv.slice(2));
const spawn   = require('child_process').spawn;
const https   = require('https');
const path    = require('path');
const fs      = require('fs');
const pkg     = require('../package.json');
const version = function () {
    let cwdPkg;
    try {
        const packageFolder = path.join(process.cwd(), 'package.json');
        cwdPkg = require(packageFolder);
    } catch (e) {}
    let version = pkg.engines.composer;
    if (cwdPkg && cwdPkg.engines && cwdPkg.engines.composer) {
        version = cwdPkg.engines.composer;
    }
    return version;
}();

function getVersionPath(version) {
    return path.join(__dirname, '..', 'vendor/composer.phar');
}

function exists(version, cb) {
    const installPath = getVersionPath(version);
    fs.stat(installPath, (err, stats) => {
        if (err) {
            cb(null, false);
            return;
        }
        cb(null, stats.isFile());
    });
}

function install(version, cb) {
    exists(version, (err, fileExists) => {
        if (fileExists) {
            if (cb) {
                cb();
            }
            return;
        }
        console.log('Downloading... Composer version ' + version);
        const installPath = getVersionPath(version);
        const file = fs.createWriteStream(installPath);
        const downloadPath = ['https://getcomposer.org/download', version,
                              'composer.phar'].join('/');
        const result = https.get(downloadPath, (res) => {
            res.pipe(file).on('close', (writeError) => cb(writeError));
        });
        result.on('error', console.error.bind(console));
    });
}

function execute(version, args, cb) {
    const versionPath = getVersionPath(version);
    const composer    = spawn('php', [versionPath].concat(args));
    composer.stdout.pipe(process.stdout);
    composer.stderr.pipe(process.stderr);
    process.stdin.pipe(composer.stdin);
    if (cb) {
        composer.on('exit', () => { cb(); });
    }
}

install(version, () => {
    console.log(`Using Composer version ${version}`);
    execute(version, argv._, () => {
        process.exit();
    });
});
