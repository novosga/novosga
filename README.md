# Novo SGA
<img src="https://raw.githubusercontent.com/novosga/art/39bf72ac/dist/logo_1200x630.png" width="20%">

Support queue management system.


## Installation

### Via Composer

Create project:

    composer create-project "novosga/novosga:v2.0.x-dev" novosga2

Run app installation command and follow instructions:

    bin/console novosga:install


### Via Docker

Documentation in the Novo SGA official [docker repository](https://github.com/novosga/docker/tree/master/novosga-2.0).


### Via Git

Clone repository:

    git clone https://github.com/novosga/novosga.git novosga2

Then follow Composer install instruction.


### Automated installation

To automated installation you need to set up the following environment variables before run `novosga:install` command:

**Database**

- DATABASE_URL
- DATABASE_PASS

**Default administrator user**

- NOVOSGA_ADMIN_USERNAME
- NOVOSGA_ADMIN_PASSWORD
- NOVOSGA_ADMIN_FIRSTNAME
- NOVOSGA_ADMIN_LASTNAME

**Default unity**

- NOVOSGA_UNITY_NAME
- NOVOSGA_UNITY_CODE

**Default priority 0**

- NOVOSGA_NOPRIORITY_NAME
- NOVOSGA_NOPRIORITY_DESCRIPTION

**Default priority 1**

- NOVOSGA_PRIORITY_NAME
- NOVOSGA_PRIORITY_DESCRIPTION

**Default attendance place**

- NOVOSGA_PLACE_NAME
