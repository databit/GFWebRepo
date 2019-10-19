<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Web Repository - I.S. "Galileo Ferraris" - Ragusa</title>

  <!-- Bootstrap Core CSS -->
  <link href="./vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- MetisMenu CSS -->
  <link href="./vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="./dist/css/sb-admin-2.min.css" rel="stylesheet">

  <!-- Code Mirror -->
  <link rel="stylesheet" href="./vendor/codemirror/lib/codemirror.css">

  <!-- Custom Fonts -->
  <link href="./vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
 </head>

 <body>
  <div id="wrapper" ng-app="gfWebRepo">
   <!-- Navigation -->
   <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0" ng-controller="navControll">
    <div class="alert alert-window" ng-class="alertWindow.type" ng-show="alertWindow.visible" id="alertWindow" role="alert">
     <i class="glyphicon pull-left h-spacer glyphicon-{{alertWindow.icon}}"><![CDATA[]]></i>
     {{alertWindow.message || trustAsHTML}}
    </div>


    <div class="navbar-header">
     <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
     </button>
     <a class="navbar-brand" href="index.php">Web Repository - I.I.S. "Galileo Ferraris" - Ragusa</a>
    </div>
    <!-- /.navbar-header -->

    <ul class="nav navbar-top-links navbar-right">
     <li ng-show="!logged">
      <a href="#!/login">
       <i class="fa fa-sign-in fa-fw"></i>
      </a>
     </li>
     <li ng-show="logged">
      <a href="#!/explore">
       <i class="fa fa-server fa-fw"></i>
      </a>
     </li>
     <li class="dropdown" ng-show="logged">
      <a class="dropdown-toggle" data-toggle="dropdown" id="uploadingFileMenu" href="">
       <i class="fa fa-upload fa-fw"></i> <i class="fa fa-caret-down"></i>
      </a>
      <ul class="dropdown-menu dropdown-tasks">
        <li>
         <a href="">
          <div ng-repeat="item in ftpUploader.queue">
           <p>
            <strong>{{item.file.name}}</strong>
            <span class="pull-right text-muted">{{ item.file.size/1024/1024|number:2 }} MB</span>
           </p>
           <div class="progress progress-striped active">
             <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{item.progress}}" aria-valuemin="0" aria-valuemax="100" ng-style="{ 'width': item.progress + '%' }">
               <span class="sr-only">{{item.progress}}% Complete (success)</span>
             </div>
           </div>
          </div>
         </a>
        </li>
       </ul>
     </li>
     <li class="dropdown" ng-show="logged">
      <a class="dropdown-toggle" data-toggle="dropdown" href="">
       <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
      </a>
      <ul class="dropdown-menu dropdown-user">
       <li><a href="#!/profile"><i class="fa fa-user fa-fw"></i> Profilo utente</a>
       </li>
       <li class="divider"></li>
       <li><a href="" ng-click="doLogout()"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
       </li>
      </ul>
      <!-- /.dropdown-user -->
     </li>
     <!-- /.dropdown -->
    </ul>
    <!-- /.navbar-top-links -->

   </nav>

   <div ng-view></div>
   <!-- /#page-wrapper -->
  </div>
  <!-- /#wrapper -->

  <!-- jQuery -->
  <script src="./vendor/jquery/jquery.min.js"></script>
  <script src="./vendor/jquery/jquery-ui.min.js"></script>

  <!-- Bootstrap Core JavaScript -->
  <script src="./vendor/bootstrap/js/bootstrap.min.js"></script>

  <!-- Metis Menu Plugin JavaScript -->
  <script src="./vendor/metisMenu/metisMenu.min.js"></script>

  <!-- AngularJS -->
  <script src="./vendor/angular/angular.min.js"></script>
  <script src="./vendor/angular/angular-animate.min.js"></script>
  <script src="./vendor/angular/angular-aria.min.js"></script>
  <script src="./vendor/angular/angular-cookies.min.js"></script>
  <script src="./vendor/angular/angular-loader.min.js"></script>
  <script src="./vendor/angular/angular-message-format.min.js"></script>
  <script src="./vendor/angular/angular-messages.min.js"></script>
  <script src="./vendor/angular/angular-parse-ext.min.js"></script>
  <script src="./vendor/angular/angular-resource.min.js"></script>
  <script src="./vendor/angular/angular-route.min.js"></script>
  <script src="./vendor/angular/angular-sanitize.min.js"></script>
  <script src="./vendor/angular/angular-touch.min.js"></script>

  <!-- Angular Upload -->
  <script src="./dist/js/angular-file-upload.min.js"></script>

  <!-- Code Mirror -->
  <script src="./vendor/codemirror/lib/codemirror.js"></script>
  <script src="./vendor/codemirror/addon/selection/active-line.js"></script>
  <script src="./vendor/codemirror/addon/edit/matchbrackets.js"></script>
  <script src="./vendor/codemirror/mode/javascript/javascript.js"></script>
  <script src="./vendor/codemirror/mode/php/php.js"></script>
  <script src="./vendor/codemirror/mode/htmlmixed/htmlmixed.js"></script>
  <script src="./vendor/codemirror/mode/css/css.js"></script>
  <script src="./vendor/codemirror/mode/python/python.js"></script>
  <script src="./vendor/codemirror/mode/sql/sql.js"></script>
  <script src="./vendor/codemirror/mode/xml/xml.js"></script>
  <script src="./vendor/codemirror/mode/clike/clike.js"></script>
  <script src="./vendor/codemirror/mode/javascript/javascript.js"></script>
  <script src="./vendor/codemirror/mode/javascript/javascript.js"></script>

  <!-- Custom Theme JavaScript -->
  <script src="./dist/js/sb-admin-2.min.js"></script>

  <!-- Custom logic -->
  <script src="./dist/js/main.js"></script>

 </body>

</html>
