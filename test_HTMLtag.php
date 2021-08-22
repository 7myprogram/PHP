<?php
require_once("HTMLtag.php");
require_once("checkfunc.php");

$master = new HTMLtag();

/* base */
$base = new HTMLtag("!DOCTYPE", $master, false);
$base->wt_att("html");

$html = new HTMLtag("html", $master);
$html->wt_att("lang", "ja");

/* head */
$head = new HTMLtag("head", $html);

$meta = new HTMLtag("meta", $head, false);
$meta->wt_att("charset", "utf-8");

$title = new HTMLtag("title", $head);
$title->insert("コメントテスト");

$link = new HTMLtag("link", $head, false);
$link->wt_atts("href", "CSS\sample1.css", "rel", "stylesheet");

$script = new HTMLtag("script", $head);
$script_inside = <<< 'EOD'
MathJax = {
chtml: {
    matchFontHeight: false
},
tex: {
    inlineMath: [['$', '$']]
}
};
EOD;
$script->insert($script_inside);

$script2 = new HTMLtag("script", $head);
$script2->wt_atts(
    "id", "MathJax-script",
    "async", NULL,
    "src", "https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"
);

/* body */
$body = new HTMLtag("body", $html);

$comrows = 10;
$comcols = 80;
$maxlength = 1000;
cken_error($_POST);
if(isset($_POST["note"])){
    $note = $_POST["note"];
    $note = mb_substr($note, 0, $maxlength);
    $note = es($note);
}
else{
    $note = "";
}

$div = new HTMLtag("div", $body);

$form = new HTMLtag("form", $div);
$form->wt_atts(
    "method", "POST",
    "action", es($_SERVER["PHP_SELF"])
);

$ul = new HTMLtag("ul", $form);
$li = new HTMLtag("li", $ul);

$textarea = new HTMLtag("textarea", $li);
$textarea->wt_atts(
    "name","note",
    "cols", $comcols,
    "rows", $comrows,
    "maxlength", $maxlength
);

$li2 = new HTMLtag("li", $ul);
$input = new HTMLtag("input", $li2, false);
$input->wt_atts(
    "type", "submit",
    "value", "コメント"
);

$master->preview();


/* if(mb_strlen($note) > 0){
    echo "<HR>";
    echo nl2br($note, false);
} */
?>