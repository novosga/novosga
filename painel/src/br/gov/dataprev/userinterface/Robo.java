
/**
 * 
 * Copyright (C) 2009 DATAPREV - Empresa de Tecnologia e Informações da Previdência Social - Brasil
 *
 * Este arquivo é parte do programa SGA Livre - Sistema de Gerenciamento do Atendimento - Versão Livre
 *
 * O SGA é um software livre; você pode redistribuí­-lo e/ou modificá-lo dentro dos termos da Licença Pública Geral GNU como 
 * publicada pela Fundação do Software Livre (FSF); na versão 2 da Licença, ou (na sua opnião) qualquer versão.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer
 * MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título "LICENCA.txt", junto com este programa, se não, escreva para a 
 * Fundação do Software Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.
 *
**/

package br.gov.dataprev.userinterface;

import java.awt.AWTException;
import java.awt.GraphicsDevice;
import java.awt.Robot;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

/**
 * @author ulysses
 *
 */
public class Robo
{
	private boolean _desativarProtecaoTela;
	private final Robot _robot;
	private final ScheduledExecutorService _ses = Executors.newScheduledThreadPool(1);
	
	public Robo(GraphicsDevice gd) throws AWTException
	{
		_robot = new Robot(gd);
	}

	
	public void setDesativarProtecaoTela(boolean b)
	{
		_desativarProtecaoTela = b;
		
		this.agendarRobo();
	}
	
	private void agendarRobo()
	{
		if (_desativarProtecaoTela)
		{
			_ses.schedule(new RoboRunnable(), 30, TimeUnit.SECONDS);
		}
	}
	
	class RoboRunnable implements Runnable
	{

		@Override
		public void run()
		{
			if (Web.getInstance().isVisible())
			{
				int y = (int) (50 * Math.random());
				_robot.mouseMove(50, y);
			}
			
			Robo.this.agendarRobo();
		}
		
	}
}
