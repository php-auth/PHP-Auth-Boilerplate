<?php
/*
| ------------------------------------------------------------------------------
| Install packages.
| ------------------------------------------------------------------------------
*/
chdir('packages');
shell_exec('npm install');
