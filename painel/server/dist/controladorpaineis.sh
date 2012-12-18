#!/bin/sh

# DATAPREV - 2009
# Script para executar o Controlador de Paineis.
 

java -Xmx256m -cp libs/*:controladorpaineis.jar br.gov.dataprev.controladorpainel.ControladorPainel
