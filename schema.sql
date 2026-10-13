CREATE DATABASE IF NOT EXISTS npc
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE npc;

CREATE TABLE IF NOT EXISTS notes (
  ref         VARCHAR(191)  NOT NULL,
  ref_raw     VARCHAR(255)  NOT NULL,
  payload     JSON          NOT NULL,
  updated_at  BIGINT        NOT NULL,
  PRIMARY KEY (ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS imports (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  source      VARCHAR(255)  DEFAULT NULL,
  added       INT           NOT NULL DEFAULT 0,
  updated     INT           NOT NULL DEFAULT 0,
  total       INT           NOT NULL DEFAULT 0,
  imported_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspectors (
  id          VARCHAR(64)   NOT NULL,
  payload     JSON          NOT NULL,
  updated_at  BIGINT        NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
