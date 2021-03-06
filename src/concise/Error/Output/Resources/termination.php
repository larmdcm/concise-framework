<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?=$title?></title>
  <style type="text/css">
   body {margin: 0px; padding:0px; font-family:"微软雅黑", Arial, "Trebuchet MS", Verdana, Georgia,Baskerville,Palatino,Times; font-size:16px;}
       .container {
         width:800px;
         height:auto;
         margin:0 auto;
         margin-top:26px;
       }
       .error_title {
          background:#F5F5F5;
          border:1px solid #ccc;
          border-radius:7px;
          padding-left:20px;
          color:#333;
          font-size:15px;
       }

       .error_message {
         background:#FFFFFF;
         border:1px solid #ccc;
         border-top:none;
         padding-top:1px;
         border-radius:7px;
       }
       .error_message p {
         color:#666;
         padding:0 5px;
       }
       .error_message strong {
         color:#3C302F;
       }
  </style>
</head>
<body>
  <div class="container">
        <div class="error_title">
           <h3><?=$message?></h3>
        </div>
  </div>
</body>
</html>