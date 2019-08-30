<?php
  for ($i = 0; $i < count($traces); $i++) {

     if (isset($traces[$i]['file']) && $traces[$i]['file'] == $errfile) {
       break;
     }
     unset($traces[$i]);
  }
  $files = file($errfile);
  $prevTotal = 10;
  $lastTotal = 10;
  $errLine = $errline;
  $errLine--;

  $outLines = [];
  $highlightLine = 0;

  for ($i = $prevTotal; $i > 0; $i--) {
      $line = $errLine - $i;
      if (isset($files[$line])) {
         array_push($outLines, [
            'line' => $line,
            'content' => htmlspecialchars($files[$line])
        ]);
      } else {
        break;
      }
  }

  $highlightLine = count($outLines) + 1;
  array_push($outLines, [
    'line' => $errLine,
    'content' =>  htmlspecialchars($files[$errLine])
  ]);
  for ($i = 1; $i <= $lastTotal; $i++) {
      $line = $errLine + $i;
      if (isset($files[$line])) {
        array_push($outLines, [
            'line' => $line,
            'content' => htmlspecialchars($files[$line])
        ]);
      } else {
         break;
      }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?=$title?></title>

  <style type="text/css">
      <?php
           $styles = [
              'static/prism/prism.css'
           ];

           foreach ($styles as $style) {
             echo file_get_contents(__DIR__ .'/' . $style);
           }
      ?>
  </style>

  <style type="text/css">

  ::selection { background-color: #E13300; color: white; }
  ::-moz-selection { background-color: #E13300; color: white; }

  body {
    background-color: #fff;
    margin: 20px;
    font: 13px/20px normal Helvetica, Arial, sans-serif;
    color: #4F5155;
  }

  a {
    color: #868686;
    background-color: transparent;
    font-weight: normal;
    cursor: pointer;
  }

  h1 {
    color: #444;
    background-color: transparent;
    border-bottom: 1px solid #D0D0D0;
    font-size: 19px;
    font-weight: normal;
    margin: 0 0 14px 0;
    padding: 14px 15px 10px 15px;
  }

  .body {
    margin: 0 5px;
  }
  .body >p {
    margin-left: 10px;
  }
  
  .code {
    font-family: Consolas, Monaco, Courier New, Courier, monospace;
    font-size: 16px;
    background-color: #f9f9f9;
    border: 1px solid #D0D0D0;
    color: #868686;
    display: block;
    margin: 14px 0 14px 0;
    padding: 12px 10px 12px 10px;
  }
  .notice {
    font-size: 22px;
    font-weight: bold;
    margin: 15px 0;
    padding: 0;
  }
  p.footer {
    text-align: right;
    font-size: 11px;
    border-top: 1px solid #D0D0D0;
    line-height: 32px;
    padding: 0 10px 0 10px;
    margin: 20px 0 0 0;
  }

  .container {
    margin: 0;
    border: 1px solid #D0D0D0;
    box-shadow: 0 0 8px #D0D0D0;
  }
  .container h1 {
    font-size: 16px;
    color: #4288ce;
  }
  .error-line {
    background: red;
  }
  .err-msg {
    color:#E13333;
    font-weight:bold;
    font-size: 24px;
    margin: 15px 0;
    padding: 0;
    text-decoration: none;
  }
  .exception-name {
    font-weight: 600;
    font-size: 18px;
    cursor: pointer;
  }

  .line-numbers-rows {
    counter-reset: itemcounter <?=$outLines[0]['line'] - 1?> !important;
  }
  .line-numbers-rows > span {
     counter-increment: itemcounter;
  }
  .line-numbers-rows > span:before {
     content: counter(itemcounter) !important;
  }
  .line-highlight {
    background: red;
    opacity: 0.3;
   }
   .call-trace {
    margin: 0 0 0 30px;
    padding: 0;
   }
   .call-trace li {
    font-size: 14px;
   }
   .call-trace span {
    cursor: pointer;
    font-weight: bold;
   }

   .variables {
    font-size: 18px;
    font-weight: bold;
   }
   .variables-empty {
    font-size: 12px;
    color: rgba(0, 0, 0, .3);
    font-weight: 100;
   }

   .table {
      width: 100%;
      margin: 12px 0;
      box-sizing: border-box;
      table-layout: fixed;
      word-wrap: break-word;
   }
   .table caption {
    text-align: left;
    font-size: 16px;
    font-weight: bold;
    padding: 6px 0;
    margin-left: 10px;
   }
   .table small {
    font-weight: 300;
    display: inline-block;
    margin-left: 10px;
    color: #ccc;
   }
   .table td:first-child {
      width: 28%;
      font-weight: bold;
      white-space: nowrap;
    }

    .table td {
      padding: 0 10px;
      vertical-align: top;
      word-break: break-all;
    }
  </style>
</head>
<body>

<div class="container">
  <h1>
     <?php
         if (empty($exceptionName)) {
          $exceptionName = 'SystemError';
         }
          echo "<span class='exception-name' title='". $exceptionName ."'>". basename($exceptionName) ."</span>" . " in ";
      ?>
     <a class="toggle" href="javascript:;" title="<?=$errfile?> line <?=$errline?>"> <?=basename($errfile)?> line <?=$errline?></a>
  </h1>
  
  <div class="body">
    
    <p>
        <a href="javascript:;" class="err-msg"><?=$errmsg?></a>
    </p>
  
  <pre data-line="<?=$highlightLine + 1?>">
    <code class="language-php line-numbers">
        <?php foreach($outLines as $item):?>
            <?=$item['content']?>
        <?php endforeach;?>
    </code>
  </pre>
  <p class="notice">Call Stack</p>
 
  <ol class="call-trace">
      <li>in <span class="toggle" title="<?=$errfile?>"> <?=basename($errfile)?></span> line <span><?=$errline?></span></li>
      <?php foreach($traces as $trace):?>
        <li>
          at&nbsp;
          <?php if (isset($trace['class'])):?>
            <span title="<?=$trace['class']?>" class="toggle"><?=basename($trace['class'])?></span>
          <?php endif?>
           <?php if (isset($trace['type'])):?>
            <span><?=$trace['type']?></span>
          <?php endif?>
          <?php if (isset($trace['function'])):?>
            <span><?=$trace['function']?></span>
          <?php endif?>
           <?php if (isset($trace['file'])):?>
            in &nbsp;<span title="<?=$trace['file']?>" class="toggle"><?=basename($trace['file'])?></span>
          <?php endif?>
           <?php if (isset($trace['line'])):?>
            line &nbsp;<span><?=$trace['line']?></span>
          <?php endif?>
        </li>
      <?php endforeach;?>
  </ol>
  <p class="notice">Environment & details</p>
  <?php
    $variables = [
        [
          'name' => 'GET Data ' . (!empty($_GET) ? '' : '<small>empty</small>'),
          'data' => !empty($_GET) ? $_GET : [],
        ],
        [
          'name' => 'POST Data ' . (!empty($_POST) ? '' : '<small>empty</small>'),
          'data' => !empty($_POST) ? $_POST : [],
        ],
        [
          'name' => 'Files ' . (!empty($_FILES) ? '' : '<small>empty</small>'),
          'data' => !empty($_FILES) ? $_FILES : [],
        ],
         [
          'name' => 'Cookies ' . (!empty($_COOKIE) ? '' : '<small>empty</small>'),
          'data' => !empty($_COOKIE) ? $_COOKIE : [],
        ],
        [
          'name' => 'Session ' . (isset($_SESSION) && !empty($_SESSION) ? '' : '<small>empty</small>'),
          'data' => isset($_SESSION) && !empty($_SESSION) ? $_SESSION : [],
        ],
         [
          'name' => 'Server/Request Data ' . (!empty($_SERVER) ? '' : '<small>empty</small>'),
          'data' => !empty($_SERVER) ? $_SERVER : [],
        ],
       [
          'name' => 'Environment Variables ' . (!empty($_ENV) ? '' : '<small>empty</small>'),
          'data' => !empty($_ENV) ? $_ENV : [],
        ],
    ];
  ?>
  <div class="variables-list">
    <?php foreach($variables as $var):?>
      <table class="table">
          <caption>
           <?=$var['name']?>
          </caption>
          <?php foreach($var['data'] as $key => $value):?>
            <tr>
              <td><?=$key?></td>
              <td>
                <?php
                    if (is_array($value) || is_object($value)) {
                        echo json_encode($value);
                    } else if (is_bool($value)) {
                        echo $value ? "true" : "false";
                    } else if (is_null($value)) {
                      echo "null";
                    } else if (is_string($value) || is_numeric($value)) {
                      echo $value;
                    } else {
                      echo "resource";
                    }
                ?>
              </td>
            </tr>
          <?php endforeach;?>
      </table>
    <?php endforeach;?>
  </div>
  </div>
</div>
  <script type="text/javascript">
      <?php
           $scripts = [
              'static/prism/prism.js'
           ];

          foreach ($scripts as $script) {
            echo file_get_contents(__DIR__ .'/' . $script);
          }
      ?>
  </script>
  <script type="text/javascript">
      function highlightReady (fn) {
           document.querySelector('.line-highlight') ? fn() : setTimeout(highlightReady.bind(this,fn),1);
      }

      highlightReady(function () {
          var resizeEvent = new Event('resize');
          window.dispatchEvent(resizeEvent);
      });

      document.querySelectorAll(".toggle").forEach(function (node) {
        node.addEventListener("dblclick",function () {
            var content = node.getAttribute("title");
            node.setAttribute("title",node.innerHTML);
            node.innerHTML = content;
        });
      });
      document.querySelector(".err-msg").addEventListener("click",function () {
        var searchUrl = "https://www.baidu.com/s?wd=php " + this.innerHTML;
        window.open(searchUrl);
      });
  </script>
</body>
</html>