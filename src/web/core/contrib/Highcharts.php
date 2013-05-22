<?php
namespace core\contrib;

/**
 * Highcharts Handler
 *
 * @author rogeriolino
 */
class Highcharts {
    
    private $id;
    private $type = 'line';
    private $title;
    private $width = 400;
    private $height = 200;
    private $tooltip;
    private $axis = array();
    private $series = array();
    private $plotOptions = array();
    
    public function __construct($id, $title = '') {
        $this->id = $id;
        $this->title = $title;
        $this->tooltip = new Tooltip();
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        $this->plotOptions = array($this->type => array());
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }
        
    public function getWidth() {
        return $this->width;
    }

    public function setWidth($width) {
        $this->width = $width;
    }

    public function getHeight() {
        return $this->height;
    }

    public function setHeight($height) {
        $this->height = $height;
    }
    
    public function getPlotOptions() {
        return $this->plotOptions;
    }
    
    public function setPlotOption($option, $value) {
        $this->plotOptions[$this->type][$option] = $value;
    }
    
    /**
     * @return Tooltip
     */
    public function getTooltip() {
        return $this->tooltip;
    }

    public function addAxis($index, array $axis) {
        if (!isset($this->axis[$index])) {
            $this->axis[$index] = array();
        }
        $this->axis[$index][] = $axis;
    }
    
    public function addSerie(Serie $serie) {
        $this->series[] = $serie;
    }
    
    public function toString() {
        if ($this->type == 'pie') {
            $this->setPlotOption('showInLegend', true);
        }
        $html = '';
        $html .= '<div id="'. $this->id .'" style="width: '. $this->width .'px; height: '. $this->height .'px;"></div>';
        $js = '<script type="text/javascript">';
        $js .= "$(document).ready(function() { new Highcharts.Chart({";
        $js .= "chart: " . json_encode(array('renderTo' => $this->id, 'type' => $this->type)) . ",";
        $js .= "title: { text: '{$this->title}' },";
        $js .= "tooltip: " . $this->tooltip->toJson() . ", ";
        foreach ($this->axis as $i => $axis) {
            $js .= "{$i}Axis: " . json_encode($axis) . ", ";
        }
        $js .= "plotOptions: " . json_encode($this->plotOptions) . ", ";
        $series = array();
        foreach ($this->series as $s) {
            $series[] = $s->toJson();
        }
        $js .= "series: [" . join(',', $series) . "]";
        $js .= "}); });</script>";
        return $html . $js;
    }
    
    public function __toString() { return $this->toString(); }
    
}

class Tooltip {
    
    private $formatter; // js function
    
    public function __construct() {
        $this->formatter = "function() { return this.series.name + ': ' + this.y; }";
    }
    
    public function getFormatter() {
        return $this->formatter;
    }

    public function setFormatter($formatter) {
        $this->formatter = $formatter;
    }
        
    public function toJson() {
        $j = '{';
        if (!empty($this->formatter)) {
            $j .= "formatter: {$this->formatter}";
        }
        return $j . '}';
    }
    
    public function __toString() {
        return $this->toJson();
    }
    
}

class Serie {
    
    private $name;
    private $data = array();
    
    public function __construct($name, array $data = array()) {
        $this->name = $name;
        $this->data = $data;
    }
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getData() {
        return $this->data;
    }

    public function setData(array $data) {
        $this->data = $data;
    }

    public function addData($data) {
        $this->data[] = $data;
    }

    public function toJson() {
        return json_encode(array(
            'name' => $this->name,
            'data' => $this->data
        ));
    }
    
    public function __toString() {
        return $this->toJson();
    }

}
