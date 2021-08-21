<?php
class HTMLtag {
    public function __construct(string $element = "", HTMLtag $object = NULL, bool $isEndtag = true){
        $this->element = $element;
        $this->isEndtag = $isEndtag;
        if ($element === ""){
            $this->isEndtag = true;
        }
        $this->attributes = array();
        $this->inside = array();
        if (!is_null($object)){
            $object->insert($this);
        }
    }

    /* Rewrite functions */
    public function wt_el(string $element){
        $this->element = $element;
    }
    public function wt_end(bool $isEndtag){
        $this->isEndtag = $isEndtag;
    }
    public function wt_att(string $label, $cont = NULL){
        if(is_null($cont)){
            $this->attributes[$label] = NULL;
        }
        else{
            $this->attributes[$label] = '"'.$cont.'"';
        }
    }
    public function wt_atts(...$label_and_cont){
        if (!(count($label_and_cont) % 2)){
            $i = 0;
            while ($i < count($label_and_cont)){
                $this->wt_att($label_and_cont[$i], $label_and_cont[++$i]);
                ++$i;
            }
        }
    }
    public function insert(...$cont){
        for ($i = 0; $i < count($cont); ++$i){
            if (is_array($cont[$i])){
                array_map(__METHOD__, $cont[$i]);
            }
            else {
                if (gettype($cont[$i]) === 'object'){
                    if(get_class($cont[$i]) === 'HTMLtag'){
                        if ($cont[$i]->get_el() !== ""){
                            if (check_inside($this, $cont[$i])){
                                echo "The process of ".$this->element."->insert(".$cont[$i]->get_el().") was skipped.\n";
                            }
                            else {
                                $this->inside[] = $cont[$i];
                            }
                        }
                    }
                }
                else if (gettype($cont[$i]) === 'string'){
                    if ($cont[$i] !== ""){
                        $this->inside[] = $cont[$i];
                    }
                }
                else{
                    $this->inside[] = $cont[$i];
                }
            }
        }
    }
    public function inserted(HTMLtag $obj){
        $obj->insert($this);
    }

    /* Delete functions */
    public function del_atts(string ...$string_array){
        for ($i = 0; $i < count($string_array); ++$i){
           unset($this->attributes[$string_array[$i]]);
        }
    }
    public function del_allatts(){
        $this->attributes = array();
    }

    public function del_ins(int ...$int_array){
        for ($i = 0; $i < count($int_array); ++$i){
            unset($this->inside[$int_array[$i]]);
        }
        $this->inside = array_values($this->inside);
    }
    public function del_allins(){
        $this->inside = array();
    }

    /* Output functions */
    public function make_tag(string $mode = ""):string{/* list, disp */
        $temp = "";
        if ($this->element !== ""){
            if ($mode === "disp" || $mode === "list"){
                self::layer_up(); $ind = "";
                for ($i = 1; $i < self::$layer; ++$i){
                    $ind = $ind."   ";
                }
                $temp = $temp.$ind;
            }
            $temp = $temp.'<'.$this->element;
            if(count($this->attributes)){
                foreach($this->attributes as $label => $cont){
                    $temp = $temp.' '.$label;
                    if($cont){
                        $temp = $temp.'='.$cont;
                    };
                }
            }
            $temp = $temp.'>';
            if ($mode === "list"){
                $temp = $temp."[".self::$list_num."]";
                self::countup();
            }
            if ($mode === "disp" || $mode === "list"){
                $temp = $temp."\n";
            }
        }
    
        if($this->isEndtag){
            for ($i = 0; $i < count($this->inside); ++$i){
                if (gettype($this->inside[$i]) === 'object'){
                    if(get_class($this->inside[$i]) === 'HTMLtag'){
                        $temp = $temp.$this->inside[$i]->make_tag($mode);
                    }
                }
                else{
                    if (gettype($this->inside[$i]) === 'string' && ($mode === "disp" || $mode === "list")){
                        $temp = $temp.$ind."    ".str_replace(PHP_EOL, "\n    ".$ind, $this->inside[$i]);
                    }
                    else{
                        $temp = $temp.$this->inside[$i];
                    }
                    if ($mode === "disp" || $mode === "list"){
                        $temp = $temp."\n";
                    }
                }
            }
            if ($this->element !== ""){
                if ($mode === "disp" || $mode === "list"){
                    $temp = $temp.$ind;
                }
                $temp = $temp.'</'.$this->element.'>';
                if ($mode === "disp" || $mode === "list"){
                    $temp = $temp."\n";
                }
            }
        }
        if ($this->element !== "" && ($mode === "disp" || $mode === "list")){
            self::layer_down();
        }      
    
        return $temp;
    }
    public function echo_tag(){
        echo $this->make_tag();
    }
    public function preview(){
        echo '<pre>';
        echo "==============================\n";
        echo htmlspecialchars($this->make_tag("disp"), ENT_QUOTES, mb_internal_encoding());
        echo "------------------------------\n";
        echo '</pre>';
        echo htmlspecialchars($this->make_tag(), ENT_QUOTES, mb_internal_encoding());
        echo '<pre>';
        echo "==============================\n";
        echo '</pre>';
    }

    public function get_el():string{
        return $this->element;
    }
    public function get_end():bool{
        return $this->isEndtag;
    }
    public function get_atts(string $label = null){
        if (is_null($label)){
            return $this->attributes;
        }
        else{
            return $this->attributes[$label];
        }
    }
    public function get_ins(int $num = -1){
        if ($num < 0){
            return $this->inside;
        }
        else {
            return $this->inside[$num];
        }
    }
    public function inslist(bool $disp = false):array{
        $temp = array();
        for ($i = 0; $i < count($this->inside); ++$i){
            if (isHTMLtag($this->inside[$i])){
                $temp[] = $this->inside[$i];
                $temp = array_merge($temp, $this->inside[$i]->inslist());
            }
        }

        if ($disp){
            self::num_reset();
            echo "<pre>";
            echo "------------------------------\n";
            echo htmlspecialchars($this->make_tag("list"), ENT_QUOTES, mb_internal_encoding());
            echo "------------------------------\n";
            echo "</pre>";
        }
        return $temp;
    }

    public function disp_info(){
        echo "<pre>";
        echo "------------------------------\n";
        echo 'Tag_name : '.$this->element."\n\n";
        echo "Options : \n";
        $this->disp_attributes();
        echo "\n";
        echo "Inside : \n";
        $this->disp_inside();
        echo "\n";
        echo "Code : \n";
        echo htmlspecialchars($this->make_tag("disp"), ENT_QUOTES, mb_internal_encoding());
        echo "------------------------------\n";
        echo "</pre>";
    }

    private function disp_attributes(){
        $temp = "";
        if(count($this->attributes)){
            foreach($this->attributes as $label => $cont){
                $temp = $temp.$label;
                if($cont){
                    $temp = $temp.'='.$cont;
                };
                $temp = $temp."\n";
            }
        }
        echo htmlspecialchars($temp, ENT_QUOTES, mb_internal_encoding());
    }

    private function disp_inside(){
        $temp = "";
        for ($i = 0; $i < count($this->inside); ++$i){
            if (gettype($this->inside[$i]) === 'object'){
                if(get_class($this->inside[$i]) === 'HTMLtag'){
                    $temp = $temp."($i)".'<'.$this->inside[$i]->get_el();
                    if(count($this->inside[$i]->get_atts())){
                        foreach($this->inside[$i]->get_atts() as $label => $cont){
                            $temp = $temp.' '.$label;
                            if($cont){
                                $temp = $temp.'='.$cont;
                            };
                        }
                    }
                    $temp = $temp.'>'."\n";
                }
            }
            else{
                $temp = $temp."($i)".$this->inside[$i]."\n";
            }
        }
        echo htmlspecialchars($temp, ENT_QUOTES, mb_internal_encoding());
    }

    private static function layer_up(){
        ++self::$layer;
    }
    private static function layer_down(){
        --self::$layer;
    }

    private static function num_reset(){
        self::$list_num = 0;
    }
    private static function countup(){
        ++self::$list_num;
    }

    /* 	Properties */
    private $element;
    private $isEndtag;
    private $attributes;
    private $inside;
    private static $layer = 0;
    private static $list_num = 0;
}

function isHTMLtag($something):bool{
    if (gettype($something) === 'object'){
        if(get_class($something) === 'HTMLtag'){
            return true;
        }
    }
    return false;
}

function check_inside(HTMLtag $host, HTMLtag $target):bool{
    for ($i = 0; $i < count($target->get_ins()); ++$i){
        if (isHTMLtag($target->get_ins($i))){
            if (($target->get_ins($i) === $host) || check_inside($host, $target->get_ins($i))){
                return true;
            }
        }
    }
    return false;
}

function analysis(string $code, HTMLtag $host = null){
    if ((strpos($code, '<?') !== false) || (strpos($code, '?>') !== false)){
        echo "fail in analysis.\n";
        return null;
    }
    if (is_null($host)){
        $host = new HTMLtag();
    }
    $code =  rwt_space($code);

    $temp = "";
    while ($code !== ""){
        if ($code[0] === '<'){
            if ($temp !== ""){
                if (trim($temp) !== ""){
                    /* if ($temp[0] === " "){
                        $temp = substr($temp, 1);
                    }
                    if ($temp[strlen($temp) - 1] === " "){
                        $temp = substr($temp, -1);
                    } */
                    $host->insert($temp);
                }
                $temp = "";
            }
            $tag_info = read_tag($code, true);
            $tag_info[0]->inserted($host);
            if ($tag_info[0]->get_end()){
                $inside_code = substr($code, $tag_info[2] + 1, $tag_info[3] - strlen($code));
                if ($tag_info[0]->get_el() === "script"){
                    $tag_info[0]->insert($inside_code);
                }
                else{
                    analysis($inside_code, $tag_info[0]);
                }
                $code = substr($code, $tag_info[4] + 1);
            }
            else{
                $code = substr($code, $tag_info[2] + 1);
            }
        }
        else{
            $temp = $temp.$code[0];
            $code = substr($code, 1);
        }
    }
    if ($temp !== ""){
        if (trim($temp) !== ""){
            if ($temp[0] === " "){
                $temp = substr($temp, 1);
            }
            if ($temp[strlen($temp) - 1] === " "){
                $temp = substr($temp, 0, strlen($temp) - 1);
            }
            $host->insert($temp);
        }
    }

    return $host;
}

function rwt_space(string $code):string{
    $code =  str_replace(PHP_EOL, " ", $code);
    $char = "\n\r\t\v\0";
    for ($i = 0; $i <strlen($char); ++$i){
        $code =  str_replace($char[$i], " ", $code);
    }

    while (strpos($code, "  ")){
        $code =  str_replace("  ", " ", $code);
    }
    $code =  str_replace("> </", "></", $code);

    return $code;
}

function read_tag(string $code, bool $isArray = false){/* [HTMLtag st_s, st_e, et_s, et_e] */
    $tag_info = search_element($code);
    $temp = new HTMLtag($tag_info[0]);
    $st_end = read_attributes($code, $tag_info[2], $temp);
    $endpos = search_end($code, $tag_info[0], $st_end + 1);
    if ($endpos[0] < 0){
        $temp->wt_end(false);
    }

    if ($isArray){
        return [$temp, $tag_info[1], $st_end, $endpos[0], $endpos[1]];
    }
    else{
        return $temp;
    }
}

function search_element(string $code):array{/* [element start end] */
    $process = 0; $temp = ""; $start;
    for ($i = 0; $i < strlen($code); ++$i){
        switch ($process){
            case 0:
                if ($code[$i] === '<'){
                    $start = $i;
                    $process = 1;
                }
                break;
            case 1:
                if ($code[$i] === ' ' || $code[$i] === '>'){
                    return [$temp, $start, $i];
                }
                $temp = $temp.$code[$i];
                break;
        }
    }
    return [$temp, -1, -1];
}

function search_end(string $code, string $element, int $start_point):array{/* [start end] */
    $temp = substr($code, $start_point);
    $str_head = strpos($temp, '</'.$element);
    if ($str_head === false){
        return [-1, -1];
    }
    else{
        for ($i = $str_head + $start_point; $i < strlen($code); ++$i){
            if ($code[$i] === '>'){
                return [$str_head + $start_point, $i];
            }
        }
    }
    return [-1, -1];
}

function read_attributes(string $code, int $start_point, HTMLtag $obj):int{
    $label = NULL;
    $cont = NULL;
    $temp = NULL;
    $quot_num = 0;
    $delimiter = null;
    $process = 0;
    for ($i = $start_point; $i < strlen($code); ++$i){
        if ($code[$i] === '>'){
            $process = 2;
        }
        switch ($process){
            case 0:
                if ($code[$i] !== ' ' && $code[$i] !== '='){
                    $label = $label.$code[$i];
                }
                if ($code[$i] === '='){
                    $process = 1;
                }
                if (($code[$i] === ' ' || $code[$i] === '=') && !is_null($label)){
                    $obj->wt_att($label);
                    $temp = $label;
                    $label = NULL;
                }
                break;
            case 1:
                if ($code[$i] === '"' || $code[$i] === "'"){
                    $delimiter = $code[$i];
                    ++$quot_num;
                }
                if($quot_num > 0 && ($code[$i] !== $delimiter)){
                    $cont = $cont.$code[$i];
                }
                if ($quot_num === 2){
                    $obj->wt_att($temp, $cont);
                    $cont = NULL;
                    $quot_num = 0;
                    $delimiter = null;
                    $process = 0;
                }
                break;
            case 2:
                if (!is_null($label)){
                    $obj->wt_att($label);
                }
                else if (!is_null($cont)){
                    $obj->wt_att($temp, $cont);
                }
                return $i;
        }
    }
    return 0;
}
?>