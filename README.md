# Novo SGA

Versão do SGA Livre reformulada para o PHP 5.3+, utilizando ORM para abstração do banco e 100% compatível com a versão de origem.

## Sobre

SGA é o acrônimo de Sistema de Gerenciamento de Atendimento, sistema desenvolvido pela Dataprev e liberado como código aberto através da versão SGA Livre.

Devido ao desenvolvimento do SGA Livre estar estagnado na mesma versão desde 2009, unindo a burocracia dos responsáveis pela comunidade no [Portal do Software Público](http://softwarepublico.gov.br/) surgiu a necessidade de criar uma nova versão para esse sistema que já roda em diversas localidades do Brasil.

A parte web foi totalmente reformulada, deixando mais leve e intuitiva, com uma interface amigável e instalação super fácil. Já no painel foi feitas melhorias para funcionar em monitores widescreen. Outras melhorias e funcionalidades estão previstas e irão ser aplicadas durante o desenvolvimento do projeto.

Tem alguma sugestão? Crie uma [issue](https://github.com/rogeriolino/novosga/issues) ou participe do desenvolvimento!


## Tecnologia

A aplicação web continua escrita em PHP, porém compatível com as versões mais novas, desfrutando de melhorias e evolução da linguagem.

- PHP 5.3
- HTML5
- CSS3
- [Doctrine PHP 2.3](http://www.doctrine-project.org/projects/orm.html)
- [jQuery 1.8+](http://jquery.com/)
- [jQuery UI 1.9+](http://jqueryui.com/)
- [Twitter Bootstrap for jQuery UI](http://addyosmani.github.com/jquery-ui-bootstrap/)
- [Highcharts](http://www.highcharts.com/)

Testado no seguintes bancos:
- [PostgreSQL](http://www.postgresql.org/)
- [MySQL](www.mysql.org)
- MS SQL


## Demo

Versão de desenvolvimento disponível online através do link: http://novosga.rogeriolino.com

- **Usuário**: admin
- **Senha**: 123456

## Instalação

Os pacotes de distribuíção são gerados via [Ant Script](http://ant.apache.org/). Bastando apenas executar dentro do diretório de cada aplicação: web ou painel (client e server).

### Web

Basta extrair o pacote da versão web (novosga-web) na raiz do seu Http Server. E depois acessar via navegador para iniciar a instalação.

*OBS*: No pacote só contém o código fonte da aplicação e SQL para criação do banco. Todas as dependências (Http Server, PHP e Banco de Dados) devem ser instaladas previamente.

### Painel

Tanto para o cliente quanto servidor são gerados arquivos JARs e lançadores destes JARs para Windows (.bat) e Linux (.sh). Logo, para poder executar o painel é necessário ter a [máquina virtual Java](http://www.java.com/getjava/) instalada.

*OBS:* Antes de executar o servidor do painel, deve-se modificar seu arquivo de configuração (server.conf).

## Autor

O autor e responsável por esta versão, [Rogério Alencar Lino Filho](http://rogeriolino.com), foi um dos principais colaboradores para a versão Livre do SGA. Fazendo parte do time de desenvolvedores de 2007 a 2008.


## Contribuições

Gostou dessa nova versão? Necessita de alguma funcionalidade ou quer apenas contribuir para manter o projeto a todo vapor?

Entre em contato conosco!
