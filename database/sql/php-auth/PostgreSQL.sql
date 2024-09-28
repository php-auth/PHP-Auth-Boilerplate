-- PHP-Auth (https://github.com/delight-im/PHP-Auth)
-- Copyright (c) delight.im (https://www.delight.im/)
-- Licensed under the MIT License (https://opensource.org/licenses/MIT)

-- Adminer 4.8.1 PostgreSQL 13.9 (Debian 13.9-0+deb11u1) dump

DROP TABLE IF EXISTS "route";
DROP SEQUENCE IF EXISTS route_id_seq;
CREATE SEQUENCE route_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."route" (
    "id" integer DEFAULT nextval('route_id_seq') NOT NULL,
    "base" character varying(20) NOT NULL,
    "dest" character varying(20) NOT NULL,
    CONSTRAINT "route_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

INSERT INTO "route" ("id", "base", "dest") VALUES
(1,	'/account',	'/');

DROP TABLE IF EXISTS "users";
DROP SEQUENCE IF EXISTS users_id_seq;
CREATE SEQUENCE users_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."users" (
    "id" integer DEFAULT nextval('users_id_seq') NOT NULL,
    "email" character varying(249) NOT NULL,
    "password" character varying(255) NOT NULL,
    "username" character varying(100),
    "status" smallint DEFAULT '0' NOT NULL,
    "verified" smallint DEFAULT '0' NOT NULL,
    "resettable" smallint DEFAULT '1' NOT NULL,
    "roles_mask" integer DEFAULT '0' NOT NULL,
    "registered" integer NOT NULL,
    "last_login" integer,
    "force_logout" integer DEFAULT '0' NOT NULL,
    CONSTRAINT "users_email_key" UNIQUE ("email"),
    CONSTRAINT "users_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

INSERT INTO "users" ("id", "email", "password", "username", "status", "verified", "resettable", "roles_mask", "registered", "last_login", "force_logout") VALUES
(1,	'admin@email.local',	'$2y$10$SmgtipaB23/HayIAUQyEGeuZjTRLpA38PpAMIiw65kVtjcHgO3r6C',	'Admin',	0,	1,	1,	1,	1637790896,	NULL,	0);

DROP TABLE IF EXISTS "users_confirmations";
DROP SEQUENCE IF EXISTS users_confirmations_id_seq;
CREATE SEQUENCE users_confirmations_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."users_confirmations" (
    "id" integer DEFAULT nextval('users_confirmations_id_seq') NOT NULL,
    "user_id" integer NOT NULL,
    "email" character varying(249) NOT NULL,
    "selector" character varying(16) NOT NULL,
    "token" character varying(255) NOT NULL,
    "expires" integer NOT NULL,
    CONSTRAINT "users_confirmations_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "users_confirmations_selector_key" UNIQUE ("selector")
) WITH (oids = false);

CREATE INDEX "email_expires" ON "public"."users_confirmations" USING btree ("email", "expires");

CREATE INDEX "user_id" ON "public"."users_confirmations" USING btree ("user_id");

INSERT INTO "users_confirmations" ("id", "user_id", "email", "selector", "token", "expires") VALUES
(1,	1,	'admin@email.local',	'gT2JRjmAVGqP275w',	'$2y$10$BMzLzZPFBfc01issfunbO.pceOWDcl4hd8BuWAlPDMEMdLEtRx.BC',	1637877297);

DROP TABLE IF EXISTS "users_remembered";
DROP SEQUENCE IF EXISTS users_remembered_id_seq;
CREATE SEQUENCE users_remembered_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."users_remembered" (
    "id" bigint DEFAULT nextval('users_remembered_id_seq') NOT NULL,
    "user" integer NOT NULL,
    "selector" character varying(24) NOT NULL,
    "token" character varying(255) NOT NULL,
    "expires" integer NOT NULL,
    CONSTRAINT "users_remembered_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "users_remembered_selector_key" UNIQUE ("selector")
) WITH (oids = false);

CREATE INDEX "user" ON "public"."users_remembered" USING btree ("user");


DROP TABLE IF EXISTS "users_resets";
DROP SEQUENCE IF EXISTS users_resets_id_seq;
CREATE SEQUENCE users_resets_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."users_resets" (
    "id" bigint DEFAULT nextval('users_resets_id_seq') NOT NULL,
    "user" integer NOT NULL,
    "selector" character varying(20) NOT NULL,
    "token" character varying(255) NOT NULL,
    "expires" integer NOT NULL,
    CONSTRAINT "users_resets_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "users_resets_selector_key" UNIQUE ("selector")
) WITH (oids = false);

CREATE INDEX "user_expires" ON "public"."users_resets" USING btree ("user", "expires");


DROP TABLE IF EXISTS "users_throttling";
CREATE TABLE "public"."users_throttling" (
    "bucket" character varying(44) NOT NULL,
    "tokens" real NOT NULL,
    "replenished_at" integer NOT NULL,
    "expires_at" integer NOT NULL,
    CONSTRAINT "users_throttling_pkey" PRIMARY KEY ("bucket")
) WITH (oids = false);

CREATE INDEX "expires_at" ON "public"."users_throttling" USING btree ("expires_at");


-- 2023-02-17 17:28:18.899384-03
