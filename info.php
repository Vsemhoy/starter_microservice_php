<?php

function h($text)
{
    return "<h3>" . $text . "</h3>";
};

function p($text)
{
    return "<p>" . $text . "</p>";
};

function b($text)
{
    return "<strong>" . $text . "</strong>";
};

function br()
{
    return "<br>";
};

function hr()
{
    return "<hr>";
};

echo "<style>
body {
    background: #3b243e;
    color: white;
    font-family: monospace;
    padding: 22px;
}
    </style>";

echo h( b("Folder: ") . "objects") . 
p("
    Each file contains an object, that describes a structure of the data
")
. hr()
. h( b("Typical data input: ") . "task objects") .
p('
{
    "user":"7",
    "tasks": [
{
  "action": "1",
  "type": "event",
  "objects": [],
  "where": [{"column": "id","value": "7","operator": "="}],
  "order": "0",
  "limit": "0",
  "offset": "0",
  "setKey": "id",
  "postactions": []
},
{
  "action": "1",
  "type": "event",
  "objects": [],
  "where": [{"column": "id","value": "7","operator": "="}],
  "order": "0",
  "limit": "0",
  "offset": "0",
  "setKey": "id",
  "postactions": []
}
]
}
')
. hr()
. h( b("Folder: ") . "controller") .
p("

")
. hr()



?>