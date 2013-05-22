.. _install:

Instalação
==================

O Novo SGA é dividido em três partes: a parte web, e painel cliente e servidor.

Antes de mais nada baixe a `última versão do Novo SGA aqui <http://novosga.org/download.php>`_.

Web
------

A parte web do Novo SGA possui como requisito principal o PHP 5.3 (ou superior), com módulo PDO instalado, um servidor HTTP (Apache2, Nginx, etc), além de um dos bancos de dados a seguir: PostgreSQL 8+, MySQL 5+ ou MS SQL.

Nessa documentação será abordada a instalação do PHP 5.3 no Apache2, e utilizando o PostgreSQL 9 como banco de dados.

Linux
~~~~~

Para distribuíções baseadas em Debian/Ubuntu:

.. code-block:: bash

    $ sudo apt-get install postgresql apache2 libapache2-mod-php5 php5 php5-pgsql


Com o servidor instalado, basta extrair o aquivo compactado (novosga-web-x.x.x.tgz), em qualquer lugar no seu servidor. Caso o seu servidor não possua interface gráfica, execute os comandos abaixo:

.. code-block:: bash

    $ cd /var/www
    $ wget http://novosga.org/releases/x.x.x/novosga-web-x.x.x.tgz .
    $ tar xfv novosga-web-x.x.x.tgz .
    $ mv novosga-web-x.x.x novosga


Windows
~~~~~~~

Que tal utilizar Linux hein?!

**TODO**: Atualizar manual com instalação servidor Apache2+PHP para Windows.


Instalador Web
~~~~~~~~~~~~~~~

Com o servidor HTTP, PHP e o banco de dados instalados, e com a aplicação já extraída no diretório do correto. Agora basta apenas acessar a aplicação via navegador web, e seguir os passos do instalador web: http://<servidor>/novosga.

Segue abaixo os screenshots das etapas do instalador:

Painel
------

Para executar o painel (tanto servidor quanto cliente), é necessário instalar o Java (JRE).


Linux
~~~~~

Para distribuíções baseadas em Debian/Ubuntu:

.. code-block:: bash

    $ sudo apt-get install openjdk-7-jre


Windows
~~~~~~~

Faça download do instalador acessando o site: `Get Java <http://www.java.com/getjava/>`_. 

Depois é só executar o instalador e seguir a instalação padrão Windows.

Executando
~~~~~~~~~~

.. warning::

    O painel (tanto servidor quanto cliente) trabalha por padrão com protocolo UDP nas portas 9999 e 8888 respectivamente. Certifique-se de que não haja bloqueios em dispositivos de rede como roteadores e firewalls

Servidor
........

O servidor de painel é um processo que roda em background entregando as senhas chamadas aos paineis cadastrados.

Basta extrair o aquivo compactado (novosga-painel-server-x.x.x.tgz), em qualquer lugar no seu servidor. Caso o seu servidor não possua interface gráfica, execute os comandos abaixo:

.. code-block:: bash

    $ wget http://novosga.org/releases/x.x.x/novosga-painel-server-x.x.x.tgz .
    $ tar xfv novosga-painel-server-x.x.x.tgz .
    $ cd novosga-painel-server-x.x.x

Com o arquivo descompactado, antes de executar, é necessário editar o arquivo **server.conf**:

.. code-block:: bash

    $ vim server.conf

Encontre e edite as seguintes entradas de acordo com a sua necessidade::

    jdbcDriver = org.postgresql.Driver
    jdbcUrl = jdbc:postgresql://127.0.0.1/sga
    jdbcUser = postgres
    jdbcPass = senha-do-usuario-postgres
    urlUnidades = http://<meu-servidor>/<caminho-para-o-novosga>/painel/get_unidades.php
    urlServicos = http://<meu-servidor>/<caminho-para-o-novosga>/painel/get_servicos.php?id_uni=%id_unidade%

.. hint::

   Nesse manual o caminho do Novo SGA deverá ser http://<servidor>/novosga


Cliente
........

O cliente do painel é aonde serão exibidas as senhas para os clientes. No caso do Linux, além do Java é necessário possuir interface gráfica instalada.

.. warning:: 

    É necessário instalar o Java7 ou superior para executar o painel cliente.


