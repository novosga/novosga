#!/bin/sh
#
# NovoSGA - Painel Server
# @author rogeriolino <rogeriolino.com>
#

java -jar {distname}.jar -Xmx256m -cp lib/* &
echo $! > pid.txt