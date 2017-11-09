# Novo SGA

Support queue management system.


## Installation

Create project using Composer:

    composer create-project "novosga/novosga:v2.0.x-dev" novosga2

Run app installation command and follow instructions:

    bin/console novosga:install


### Automated installation

To automated installation you need to set the following environment variables before install command run:

**Database**

- **DATABASE_URL**
- **DATABASE_PASS**

**Default administrator user**

- **NOVOSGA_ADMIN_USERNAME**
- **NOVOSGA_ADMIN_PASSWORD**
- **NOVOSGA_ADMIN_FIRSTNAME**
- **NOVOSGA_ADMIN_LASTNAME**

**Default unity**

- **NOVOSGA_UNITY_NAME**
- **NOVOSGA_UNITY_CODE**

**Default priority 0**

- **NOVOSGA_NOPRIORITY_NAME**
- **NOVOSGA_NOPRIORITY_DESCRIPTION**

**Default priority 1**

- **NOVOSGA_PRIORITY_NAME**
- **NOVOSGA_PRIORITY_DESCRIPTION**

** Default attendance place**

- **NOVOSGA_PLACE_NAME**