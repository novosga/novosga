#!/bin/sh
#
# NovoSGA - Painel Server
#
# @author rogeriolino <rogeriolino.com>
#

java -Xmx256m -cp libs/*:{distname}.jar br.gov.dataprev.controladorpainel.ControladorPainel
