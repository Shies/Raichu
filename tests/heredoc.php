<?php

$foo = isset($foo) ? $foo : null;
$bar = isset($bar) ? $bar : null;

// dummy example
function welcome($p1, $p2)
{
        echo "hello";
        echo $p1;
?>
<?php
        echo "world";
        echo "$p2";
?>

<?php
    // heredoc for php and html
    $nowdoc = <<<HTML
<div id="container">welcome flashing...</div>
HTML;

    return $nowdoc;
}

// syntax i/o
echo welcome($foo, $bar);