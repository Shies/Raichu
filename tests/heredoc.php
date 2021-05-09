<?php

// dummy example
function jumper_example()
{
    global $foo, $bar;

    // heredoc for php and html
    $nowdoc = <<<PHP
        echo "hello";
        echo $foo;
?>
<?php
        echo "world";
        echo "$bar";
?>
PHP;

    return $nowdoc;
}

// syntax i/o
echo jumper_example();