<?php
namespace core;
/* Arquivo de configuracao inicial */
class Config {
    const IS_DEV = true;
    const SGA_INSTALLED = true;
//    const DB_TYPE = "mssql";
//    const DB_HOST = "mutum.pmv.local";
//    const DB_PORT = "";
//    const DB_USER = "usr_sga";
//    const DB_PASS = "usrsga1q2w3e";
//    const DB_NAME = "bdsga";
    const DB_TYPE = "pgsql";
    const DB_HOST = "localhost";
    const DB_PORT = "5432";
    const DB_USER = "postgres";
    const DB_PASS = "12345";
    const DB_NAME = "sga";
}
