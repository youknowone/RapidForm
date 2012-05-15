<?

class RFWidget {
    public $name = '';
    public $attributes = array();

    function __construct($title=null) {
        if ($title) $this->title = $title;
    }

    function __get_title() {
        return isset($this->attributes['title']) ? $this->attributes['title'] : $this->name;
    }

    function __get_field_id() {
        return "id_".$this->name;
    }

    function __get_field_attributes() {
        $field_attributes = array_merge(array('id' => $this->field_id, 'name' => $this->name), $this->attributes);
        $output = '';
        foreach ($field_attributes as $key => $val) {
            $output .= " $key=\"$val\"";
        }
        return $output;
    }

    function __get_field() {
        return "<input{$this->field_attributes} />";
    }

    function __get_label_id() {
        return "id_".$this->name."_label";
    }

    function __get_label() {
        return sprintf('<label id="%s" name="%s">%s</label>', $this->label_id, $this->name, $this->title);
    }
    
    function __get($name) {
        if (method_exists($this, $func = "__get_" . $name))
            return $this->$func();
        else if (isset($this->$name))
            return $this->$name;
        return $this->attributes[$name];
    }

    function __set($name, $value) {
        if (method_exists($this, $func = "__set_" . $name))
            return $this->$func($value);
        else if (isset($this->$name))
            return $this->$name = $value;
        return $this->attributes[$name] = $value;
    }
}

class RFPlainWidget extends RFWidget {
    function __get_field() {
        return sprintf('<span id="%s">%s</span>', $this->field_id, $this->value);
    }
}

class RFHiddenWidget extends RFWidget {
    function __construct($title=null) {
        parent::__construct($title);
        $this->type = 'hidden';
    }
}   

class RFTextWidget extends RFWidget {
    function __construct($title=null, $readonly=null) {
        parent::__construct($title);
        $this->type = 'text';
        if ($readonly) $this->readonly = "readonly";
    }
}

class RFButtonWidget extends RFWidget {
    function __construct($title=null) {
        parent::__construct($title);
        $this->type = 'button';
    }
}

class RFSubmitWidget extends RFWidget {
    function __construct($image_path=null) {
        if ($image_path) {
            $this->type = 'image';
            $this->src = $image_path;
        } else {
            $this->type = 'submit';
        }
    }
}

class RFSelectWidget extends RFWidget {
    public $candidates;
    public $selector;

    function __construct(array $candidates, $title=null, $readonly=null, $selector=null) {
        parent::__construct($title);
        $this->candidates = $candidates;
        $this->selector = $selector;
    }

    function __get_field() {
        $outputs = array();
        foreach ($this->candidates as $key => $candidate) {
            $selector = $this->selector;
            $option = !is_null($selector) ? $selector($key, $candidate) : $candidate;
            $outputs[] = sprintf('<option value="%s"%s>%s</option>',
                                 $key, $this->value == $key ? ' selected="selected"' : '', $option);
        }
        return "<select{$this->field_attributes}>".implode($outputs)."</select>";
    }
}

class RFRow {
    public $name;
    public $title;

    function __construct($title, array $widgets, $formatter=null) {
        $this->title = $title;
        $this->widgets = $widgets;
        $this->formatter = $formatter;

        foreach ($this->widgets as $key => $widget) {
            $widget->name = $key;
        }
    }

    function format() {
        $outputs = array();
        foreach ($this->widgets as $widget) {
            $outputs[] = $widget->field;
        }
        
        if ($this->formatter === null)
            return implode(' ', $outputs);
        if (is_string($this->formatter))
            return vsprintf($this->formatter, $outputs);
        return $this->formatter($outputs);
    }

    public function as_dl() {
        $output = $this->format();
        return "<dd>{$this->title}</dd><dt>{$output}</dt>";
    }

    public function set_data($data) {
        foreach ($this->widgets as $key => $widget) {
            if (isset($data[$key])) {
                $widget->value = $data[$key];
            } else {
                $widget->value = null;
            }
        }
    }
}

class RFSingleRow extends RFRow {
    function __construct($title, RFWidget $widget, $key=0) {
        parent::__construct($title ? $title : $widget->title, array($key => $widget));
    }
}

class RFSpaceRow extends RFRow {
    function __construct() { $this->widgets = array(); }

    function as_dl() {
        return '<br style="clear:both" /><br/>';
    }
}

class RFForm {
    public $rows;

    function __construct(array $rows, array $data = array()) {
        foreach ($rows as $key => $row) {
            if (is_a($row, 'RFWidget')) {
                $rows[$key] = new RFSingleRow(null, $row, $key);
            }
            $rows[$key]->name = $key;
        }
        $this->rows = $rows;
        $this->set_data($data);
    }

    function __get($name) {
        return $this->rows[$name];
    }

    function __set($name, $value) {
        return $this->rows[$name] = $value;
    }

    public function set_data($data) {
        foreach ($this->rows as $key => $row) {
            $row->set_data($data);
        }
    }

    public function as_dl() {
        $out = '';
        foreach ($this->rows as $row) {
            $out .= $row->as_dl()."\n";
        }
        return $out;
    }
}

