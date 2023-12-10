<?php
//Starting PHP session
session_start();

include "config.php";
?>
<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Web Repository - <?php echo SERVER_NAME; ?></title>

   <!-- Bootstrap core JavaScript-->
   <script defer src="./vendor/jquery/jquery.min.js"></script>
   <script defer src="./vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

   <!-- Core plugin JavaScript-->
   <script defer src="./vendor/jquery-easing/jquery.easing.min.js"></script>

   <!-- Custom scripts for all pages-->
   <script defer src="./js/sb-admin-2.min.js"></script>

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
  <script src="./js/angular-file-upload.min.js"></script>

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

  <!-- QR Code -->
  <script src="./vendor/qrcode/qrcode.min.js"></script>

   <!-- Business core script -->
   <script defer src="./js/main.js"></script>

  <!-- Custom fonts for this template-->
  <link href="./vendor/font-awesome/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="./css/sb-admin-2.min.css" rel="stylesheet">

  <!-- Code Mirror -->
  <link rel="stylesheet" href="./vendor/codemirror/lib/codemirror.css">

  <link rel="icon" href="./img/favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="./img/favicon.ico">

 </head>

 <body id="page-top" ng-app="webRepo">
  <!-- Loading layer -->
  <div class="modal fade show d-block" ng-show="loading">
   <div class="loader"></div>
  </div>
  <div class="modal-backdrop fade show" ng-show="loading"></div>

   <!-- Alert message box -->
   <div class="alert alert-window alert-dismissible {{alertWindow.type}}" ng-show="alertWindow.visible" id="alertWindow" role="alert">
    <i class="fas fa-{{alertWindow.icon}} float-left m-2 mr-4"></i>
    <span ng-bind-html="alertWindow.message | trustAsHtml"></span>
     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
       <span aria-hidden="true">&times;</span>
     </button>
    </div>

  <div id="wrapper">
   <!-- Sidebar -->
   <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar" ng-controller="navControll">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
     <div class="sidebar-brand-icon">
      <img src="./img/logo.png" alt="Logo" style="width: 3rem;"/>
     </div>
     <div class="sidebar-brand-text mx-3">Web Repo</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
     <a class="nav-link" href="index.html">
      <i class="fas fa-fw fa-server"></i>
      <span><?php echo SERVER_NAME; ?></span>
     </a>
    </li>

    <li class="nav-item" ng-show="!logged">
     <a href="#!/login" class="nav-link">
      <i class="fas fa-fw fa-sign-in"></i>
      <span>Login</span>
     </a>
    </li>

    <li class="nav-item" ng-show="logged">
     <a href="#!/profile" class="nav-link">
      <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
      <span class="ml-2 d-none d-lg-inline small">{{username}}</span>
     </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item">
     <a href="" class="nav-link collapsed" data-toggle="collapse" data-target="#collapseWebSpace" aria-expanded="true" aria-controls="collapseWebSpace">
      <i class="fas fa-fw fa-globe"></i>
      <span>Web Space</span>
     </a>
     <div id="collapseWebSpace" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
       <a href="/users/{{username}}" class="collapse-item"  target="_blank">
        <i class="fas fa-fw fa-link mr-1"></i> Apri finestra
       </a>
       <a href="" class="collapse-item" ng-click="createQRCode()" data-toggle="modal" data-target="#qrCodelLinkWindow">
        <i class="fas fa-fw fa-qrcode mr-1"></i> QR Code
       </a>
      </div>
     </div>
    </li>

    <li class="nav-item" ng-show="logged">
     <a href="#!/explore" class="nav-link">
      <i class="fas fa-fw fa-server"></i>
      <span>Esplora files</span>
     </a>
    </li>

    <li class="nav-item">
     <a href="/phpmyadmin" class="nav-link" target="_blank">
      <i class="fas fa-fw fa-database"></i>
      <span>PhpMyAdmin</span>
     </a>
    </li>

    <li class="nav-item">
     <a href="" class="nav-link" data-toggle="modal" data-target="#logoutWindow">
      <i class="fas fa-fw fa-sign-out-alt"></i>
      <span>Esci</span>
     </a>
    </li>


    <!-- Divider -->
    <hr class="sidebar-divider" ng-show="logged">

    <!-- Heading -->
    <div class="sidebar-heading" ng-show="logged">
     Admin
    </div>

    <li class="nav-item" ng-show="logged">
     <a href="#!/users" class="nav-link">
      <i class="fas fa-fw fa-users"></i>
      <span>Utenti</span>
     </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
     <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
   </ul>

   <!-- Content Wrapper -->
   <div id="content-wrapper" class="d-flex flex-column">
    <!-- Main Content -->
    <div id="content" ng-view>
    </div>

    <!-- Footer -->
    <footer class="sticky-footer bg-white">
     <div class="container my-auto">
      <div class="copyright text-center my-auto">
       <span>
        Copyright &copy;
        <a href="https:///www.danielecontarino.it" target="_blank">Daniele Contarino</a> - 
        <a href="https://github.com/databit/WebRepo" target="_blank">Web Repo Github Project</a>
       </span>
      </div>
     </div>
    </footer>
   </div>
  </div>

   <!-- Scroll to Top Button-->
   <a class="scroll-to-top rounded" href="body">
    <i class="fas fa-angle-up"></i>
   </a>

   <!-- Force change Modal-->
   <div id="forceChangePasswordWindow" class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="forceChangePasswordLabel" aria-hidden="true" ng-controller="loginControll">
    <div class="modal-dialog">
     <div class="modal-content">
      <div class="modal-header">
       <h5 class="modal-title" id="forceChangePasswordLabel">Cambio password obbligatiorio</h5>
      </div>
      <div class="modal-body">
       <div class="row my-1">
        <div class="col-12 text-center">
         <h5>Devi cambiare la password perche&eacute; questo &egrave; il tuo primo accesso a questa applicazione o ti &egrave; stata resettata la password. </h5>
        </div>
        <div class="col-12">
         <label>Nuova Password</label>
         <div class="input-group">
          <input type="password" class="form-control" ng-model="newPassword" id="newPassword" placeholder="inserisci la password" aria-label="inserisci la password"/>
          <div class="input-group-append">
           <button class="btn btn-outline-secondary" type="button" ng-click="togglePassword('newPassword')">
            <i class="fas" ng-class="{'fa-eye': !passwordShowed, 'fa-eye-slash': passwordShowed}"></i>
           </button>
          </div>
         </div>
         <div class="invalid-feedback">la password &egrave; necessaria!</div>
        </div>
       </div>
      </div>

      <div class="modal-footer">
       <button class="btn btn-warning mr-2" ng-click="changePassword()">
        <i class="fa-solid fa-arrows-rotate mr-2" aria-hidden="true"></i> Cambia password
       </button>
      </div>
     </div>
    </div>
   </div>
 
   <!-- QRCode Modal-->
   <div id="qrCodelLinkWindow" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="qrCodelLinkLabel" aria-hidden="true">
    <div class="modal-dialog">
     <div class="modal-content">
      <div class="modal-header">
       <h5 class="modal-title" id="qrCodelLinkLabel">
        QR Code dello spazio web di
        <a href="{{window.location.protocol +'//'+ window.location.hostname +'/users/'+ username}}" target="_blank">{{username}}</a>
        </h5>
       <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
        <span aria-hidden="true">&times;</span>
       </button>
      </div>
      <div class="modal-body">
      <div class="row"></div>
       <div class="mx-auto" style="width: min-content;"id="qrcode"></div>
      </div>
     </div>
    </div>
   </div>

   <!-- Logout Modal-->
   <div class="modal fade" id="logoutWindow" tabindex="-1" role="dialog" aria-labelledby="logoutLabel" aria-hidden="true" ng-controller="loginControll">
    <div class="modal-dialog" role="document">
     <div class="modal-content">
      <div class="modal-header">
       <h5 class="modal-title" id="logoutLabel">Logout da <?php echo SERVER_NAME;?></h5>
       <button class="close" type="button" data-dismiss="modal" aria-label="Chiudi">
        <span aria-hidden="true">×</span>
       </button>
      </div>
      <div class="modal-body">
       <p class="lead mt-4 text-center">
        <i class="fas fa-exclamation-triangle text-warning mr-2" aria-hidden="true"></i>
        <strong>Stai per effettuare la disconnessione dall'account. Vuoi proseguire?</strong>
       </p>
      </div>
      <div class="modal-footer">
       <button type="submit" class="btn btn-secondary mr-2" data-dismiss="modal">
         <i class="far fa-thumbs-up mr-2"></i> Rimani
       </button>
       <button type="submit" class="btn btn-danger" ng-click="doLogout()">
         <i class="fas fa-sign-out-alt mr-2"></i> Esci
       </button>
      </div>
     </div>
    </div>
   </div>
 </body>
</html>