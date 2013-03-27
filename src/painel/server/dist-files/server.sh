#!/bin/sh
#
# NovoSGA - Painel Server
# @author rogeriolino <rogeriolino.com>
#

java -Xmx256m -cp lib/*:{distname}.jar org.novosga.painel.server.Main &
echo $! > pid.txt