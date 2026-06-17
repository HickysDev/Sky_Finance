CREATE TABLE IF NOT EXISTS usuarios (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(100)  NOT NULL,
    email        VARCHAR(150)  NOT NULL UNIQUE,
    senha_hash   VARCHAR(255)  NOT NULL,
    ativo        TINYINT(1)    NOT NULL DEFAULT 1,
    ultimo_login DATETIME      NULL,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS login_tentativas (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    ip               VARCHAR(45)  NOT NULL,
    tentativas       INT          NOT NULL DEFAULT 1,
    bloqueado_ate    DATETIME     NULL,
    ultima_tentativa DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ip (ip)
);
