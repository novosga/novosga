package br.gov.dataprev.painel.event;

import br.gov.dataprev.userinterface.Web;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;
import java.util.logging.Logger;

/**
 *
 * @author DATAPREV
 */
public class PainelMouseListener extends MouseAdapter {
    
    private static final Logger LOG = Logger.getLogger(Web.class.getName());

    /* (non-Javadoc)
     * @see java.awt.event.MouseAdapter#mousePressed(java.awt.event.MouseEvent)
     */
    @Override
    public void mousePressed(MouseEvent e) {
        int b123mask = MouseEvent.BUTTON1_DOWN_MASK | MouseEvent.BUTTON2_DOWN_MASK | MouseEvent.BUTTON3_DOWN_MASK;
        int result = e.getModifiersEx() & b123mask;
        if (Integer.bitCount(result) >= 2) {
            Web.getInstance().setVisible(false);
        }
        e.consume();
        LOG.fine("Painel ocultado por atalho do mouse.");
    }
}