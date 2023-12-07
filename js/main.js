var webRepoApp = angular.module('webRepo', ['ngRoute', 'angularFileUpload']); //, 'ngMaterial', , 

webRepoApp.config(function($httpProvider, $routeProvider, $locationProvider) {
    $routeProvider
        .when('/login', {
                templateUrl: './views/login.html',
                controller: loginControll
            })
        .when('/profile', {
                templateUrl: './views/profile.html',
                controller: profileControll
            })
        .when('/explore', {
                templateUrl: './views/explore.html',
                controller: exploreControll
            })
        .when('/users', {
                templateUrl: './views/users.html',
                controller: usersControll
            })
        .otherwise({
                templateUrl: './views/login.html',
                controller: loginControll
            });

    //Enable remove domain calls
    $httpProvider.defaults.useXDomain = true;

    // Use x-www-form-urlencoded Content-Type
    //$httpProvider.defaults.headers.post['Content-Type'] = 'application/json'; //'application/x-www-form-urlencoded;charset=utf-8';

    //Remove the header used to identify ajax call  that would prevent CORS from working
    delete $httpProvider.defaults.headers.common['X-Requested-With'];
    
    //$locationProvider.html5Mode(true);


})

webRepoApp.run(function($rootScope) {
    $rootScope.loading = false;
});

/** CONTROLLERS **/
webRepoApp.controller('navControll', function($scope, $rootScope, $http, $location, $sce, FileUploader) { 
    $rootScope.alertWindow = {type:'', message: '', visible: false};
    $rootScope.logged = false;
    $rootScope.username = null;
    $rootScope.ftpUploader = new FileUploader({ url: 'ws/explore.php', alias: 'upload'});

    //Tools
    $rootScope._showAlert = function(typeMessage, icon, messageText) {
        $rootScope.alertWindow = {type:'alert-'+ typeMessage, icon: icon, message: messageText, visible: true};

        $('#alertWindow')
            .slideDown("slow", "easeOutBounce")
            .delay(5000)
            .slideUp("slow", "easeOutBounce", function(){ $rootScope.alertWindow.visible = false; });
    };
});

webRepoApp.controller('loginControll', loginControll);

function loginControll($scope, $rootScope, $http, $route, $routeParams, $location, $filter) {
    $scope.username = '';
    $scope.password = '';
    $rootScope.logged = false;
    $rootScope.username = null;

    $http.post('./ws/profile.php', { cmd: 'is-logged' })
         .then(function(response) {
            if(response.status == 200 && response.data.success){
                $rootScope.logged = true;
                $rootScope.username = response.data.username;
                if($location.path() == '/login')
                    $location.path('/explore');
            }
        });
    
    $scope.doLogin = function() {
        $rootScope.loading = true;

        $http.post('./ws/profile.php', 
             {  cmd: 'login', 
                username: $scope.username, 
                password: $scope.password })
                
             .then(function(response) {
                $rootScope.loading = false;
    
                if(response.status == 200 && response.data.success){
                    $rootScope.logged = true;
                    $rootScope.username = $scope.username;
                    $location.path('/explore');
                
                } else {
                    $scope._showAlert('danger', 'remove', "Nome utente o password non validi.");
                }
            });
    };

    $scope.doLogout = function() {
        $rootScope.loading = true;

        $http.post('./ws/profile.php', { cmd: 'logout' })
             .then(function(response) {
                $rootScope.loading = false;
                if(response.status == 200 && response.data.success){
                    $('#logoutWindow').modal('hide');
                    $rootScope.logged = false;
                    $rootScope.username = null;
                    $location.path('/login');
                }
            });
    };
}

function profileControll($scope, $http, $timeout) {
    $scope.profile = Array();

    //Get the user profile
    $http.post('./ws/profile.php', 
            {   'cmd': 'get-profile' })
        .then(function(response) {
            $scope.profile =  response.data;
        });

    $scope.editProfile = function() {
        $http.post('./ws/profile.php', 
                {   'cmd': 'edit-profile',
                    'passwordNew': $scope.profile.passwordNew,
                    'passwordConfirm': $scope.profile.passwordConfirm
                })
            .then(function(response) {
                if(response.data.success){
                    $scope.profile.passwordNew = '';
                    $scope.profile.passwordConfirm = '';

                    $scope._showAlert('success', 'ok', "Il profilo è stato modificato con successo!");
                    $('#editUserWindow').modal('hide');

                 } else {
                    if(responseData['error'] == 'confirm-not-match')
                       $scope._showAlert('warning', 'warning-sign', "La password non coincide con la password di conferma");
                            
                    else if(responseData['error'] == 'password-too-short')
                       $scope._showAlert('warning', 'warning-sign', "La password deve avere almeno 8 caratteri");

                 }       
            });
    };
};

function exploreControll($scope, $rootScope, $http, $timeout, $location, FileUploader) {
    $scope.currentPaths = [];
    $scope.files = [];
    $scope.folders = [];
    $scope.auxItem = {};
    $scope.search = '';
    $scope.renameFilename = '';
    $scope.moving = {
        paths: [],
        folders: []
    };

    $rootScope.loading = false;
    $scope.loadingMove = false;
    $scope.showEditor = false;

    $scope.editor = CodeMirror.fromTextArea(document.getElementById("CodeMirrorEditor"), {
            mode: "javascript",
            lineNumbers: true,
            lineWrapping: true,
            styleActiveLine: true,
            matchBrackets: true,
            extraKeys: {"Ctrl-Q": function(cm){ cm.foldCode(cm.getCursor()); }},
            foldGutter: true,
            gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
        });
    
    $scope.extensions = {
        'php':      { icon : 'file-code', editable: true },
        'html':     { icon : 'file-code', editable: true },
        'htm':      { icon : 'file-code', editable: true },
        'htaccess': { icon : 'file-code', editable: true },
        'css':      { icon : 'file-code', editable: true },
        'js':       { icon : 'file-code', editable: true },
        'py':       { icon : 'file-code', editable: true },
        'asp':      { icon : 'file-code', editable: true },
        'sql':      { icon : 'file-code', editable: true },
        'xml':      { icon : 'file-code', editable: true },
        'xsl':      { icon : 'file-code', editable: true },
        'c':        { icon : 'file-code', editable: true },
        'cpp':      { icon : 'file-code', editable: true },
        'cxx':      { icon : 'file-code', editable: true },
        'java':     { icon : 'file-code', editable: true },
        'txt':      { icon : 'file-text', editable: true },
        'ini':      { icon : 'file-text', editable: true },
        'doc':      { icon : 'file-word', editable: false },
        'docx':     { icon : 'file-word', editable: false },
        'odt':      { icon : 'file-word', editable: false },
        'xls':      { icon : 'file-excel', editable: false },
        'xlsx':     { icon : 'file-excel', editable: false },
        'ods':      { icon : 'file-excel', editable: false },
        'ppt':      { icon : 'file-powerpoint', editable: false },
        'pptx':     { icon : 'file-powerpoint', editable: false },
        'odp':      { icon : 'file-powerpoint', editable: false },
        'jpg':      { icon : 'file-image', editable: false },
        'png':      { icon : 'file-image', editable: false },
        'gif':      { icon : 'file-image', editable: false },
        'bmp':      { icon : 'file-image', editable: false },
        'tiff':     { icon : 'file-image', editable: false },
        'zip':      { icon : 'file-archive', editable: false },
        'rar':      { icon : 'file-archive', editable: false },
        'gzip':     { icon : 'file-archive', editable: false },
        '7z':       { icon : 'file-archive', editable: false },
        'gz':       { icon : 'file-archive', editable: false },
        'xz':       { icon : 'file-archive', editable: false },
        'bz2':      { icon : 'file-archive', editable: false }
    }

    $scope.list = function (path, isMoveFile){
        if(typeof isMoveFile == "undefined")
            isMoveFile = false;

        if((!$rootScope.loading && !isMoveFile) || (!$rootScope.loading && isMoveFile)){
            if(isMoveFile)
                $scope.loadingMove = true
            else
                $rootScope.loading = true;

            var realPath = isMoveFile ? $scope.moving.paths.join('/') : $scope.currentPaths.join('/');

            // Get files and folders of root directory
            $http.post('./ws/explore.php', 
                 {  cmd: 'list', 
                    path: realPath +'/'+ path })
                    
                 .then(function(response) {
                    if(response.status == 200 && response.data.success){
                        if(isMoveFile){
                            $scope.moving.folders = response.data.folders;
                            if(path != '')
                                $scope.moving.paths.push(path);

                        } else {
                            $scope.folders = response.data.folders;
                            $scope.files = response.data.files;
                            if(path != '')
                                $scope.currentPaths.push(path);
                            
                            // Calculate the extension
                            for (key in $scope.files)
                                $scope.files[key].ext = $scope.files[key].name.slice((($scope.files[key].name[0] != '.') ? Math.max(0, $scope.files[key].name.lastIndexOf(".")) || Infinity : 0) + 1);
                        }
                    } else {
                        $scope.files = [];
                        if(isMoveFile)
                            $scope.moving.folders = [];
                        else 
                            $scope.folders = [];

                        if(!response.data.is_logged) {
                            $scope._showAlert('warning', 'alert', "La sessione è scaduta. Effettuare di nuovo l'accesso al server");
                            //$rootScope.logged = false;
                            //$rootScope.username = null;
                            //$location.path('/login');
                        
                        } else
                            $scope._showAlert('warning', 'alert', "E' accorso un errore durante la comunicazione con il server");
                    }
                    $scope.search = '';
                    if(isMoveFile)
                        $scope.loadingMove = false
                    else
                        $rootScope.loading = false;

                });
        }
    };

    $scope.listDirFromIndex = function (index, isMoveFile){
        if(typeof isMoveFile == "undefined")
            isMoveFile = false;

        if((!$rootScope.loading && !isMoveFile) || (!$rootScope.loading && isMoveFile)){
            if(isMoveFile)
                $scope.loadingMove = true
            else
                $rootScope.loading = true;

            var realPath = '';
            for(i=0; i<=index; i++)
                realPath = realPath + (isMoveFile ? $scope.moving.paths[i] : $scope.currentPaths[i]) + '/';
                
            // Get files and folders of root directory
            $http.post('./ws/explore.php', 
                 {  cmd: isMoveFile ? 'list-folders' : 'list', 
                    path: realPath })
                    
                 .then(function(response) {
                    if(response.status == 200 && response.data.success){
                        if(isMoveFile){
                            $scope.moving.folders = response.data.folders;
                            $scope.moving.paths = $scope.moving.paths.slice(0, index+1);

                        } else {
                            $scope.folders = response.data.folders;
                            $scope.files = response.data.files;
                            $scope.currentPaths = $scope.currentPaths.slice(0, index+1);

                            // Calculate the extension
                            for (key in $scope.files)
                                $scope.files[key].ext = $scope.files[key].name.slice((($scope.files[key].name[0] != '.') ? Math.max(0, $scope.files[key].name.lastIndexOf(".")) || Infinity : 0) + 1);
                        }
                    } else {
                        if(isMoveFile)
                            $scope.moving.folders = [];

                        else {
                            $scope.files = [];
                            $scope.folders = [];
                        }
                        
                        if(!response.data.is_logged) {
                            $scope._showAlert('warning', 'alert', "La sessione è scaduta. Effettuare di nuovo l'accesso al server");
                            $rootScope.logged = false;
                            $rootScope.username = null;
                            $location.path('/login');
                        
                        } else
                            $scope._showAlert('warning', 'alert', "E' accorso un errore durante la comunicazione con il server");
                    }
                    $scope.search = '';
                    if(isMoveFile)
                        $scope.loadingMove = false
                    else
                        $rootScope.loading = false;

                });
        }
    }

    $scope.saveFile = function(){
        if(!$rootScope.loading){
            $rootScope.loading = true;

            var realPath = $scope.currentPaths.join('/');

            // Get files and folders of root directory
            $http.post('./ws/explore.php', 
                 {  cmd: 'write', 
                    path: realPath,
                    filename: $scope.auxItem.file.name,
                    buffer: $scope.editor.getValue() })
                    
                 .then(function(response) {
                    if(response.status == 200 && response.data.success){
                        $scope.showEditor = false;
                        $scope._showAlert('success', 'ok', "Il file <b>"+ $scope.auxItem.file.name + "</b> è stato modificato con successo");
                        
                    } else {
                        if(!response.data.is_logged) {
                            $scope._showAlert('warning', 'alert', "La sessione è scaduta. Effettuare di nuovo l'accesso al server");
                            $rootScope.logged = false;
                            $rootScope.username = null;
                            $location.path('/login');
                        
                        } else
                            $scope._showAlert('danger', 'remove', "Si è verificato un errore durante il salvataggio del file");
                    }
                    
                    $rootScope.loading = false;
                });
        }
    };

    $scope.create = function(){
        if(!$rootScope.loading){
            $rootScope.loading = true;
            $('#newWindow').modal('hide');

            var realPath = $scope.currentPaths.join('/');

            // Get files and folders of root directory
            $http.post('./ws/explore.php', 
                 {  cmd: 'create', 
                    path: realPath,
                    filename: $scope.auxItem.filename,
                    isFile: $scope.auxItem.isFile })
                    
                 .then(function(response) {
                    if(response.status == 200 && response.data.success){
                        if($scope.auxItem.isFile)
                            $scope.files.push({ name: $scope.auxItem.filename});
                        else
                            $scope.folders.push({ name: $scope.auxItem.filename});

                        $scope._showAlert('success', 'ok', ($scope.auxItem.isFile ? ' Il file' : 'La cartella') + " <b>"+ $scope.auxItem.filename + "</b> è "+ ($scope.auxItem.isFile ? 'stato creato' : 'stata creata') +" con successo!");
                        
                    } else {
                        if(!response.data.is_logged) {
                            $scope._showAlert('warning', 'alert', "La sessione è scaduta. Effettuare di nuovo l'accesso al server");
                            $rootScope.logged = false;
                            $rootScope.username = null;
                            $location.path('/login');
                        
                        } else
                            $scope._showAlert('danger', 'remove', "Si è verificato un errore durante la creazione del"+ ($scope.auxItem.isFile ? ' file' : 'la cartella'));
                    }
                    
                    $scope.search = '';
                    $rootScope.loading = false;
                });
        }
    };

    $scope.rename = function(){
        if(!$rootScope.loading){
            $rootScope.loading = true;
            $('#renameWindow').modal('hide');

            var realPath = $scope.currentPaths.join('/');

            // Get files and folders of root directory
            $http.post('./ws/explore.php', 
                 {  cmd: 'rename', 
                    path: realPath,
                    from: $scope.auxItem.file.name,
                    to: $scope.renameFilename })
                    
                 .then(function(response) {
                    if(response.status == 200 && response.data.success){
                        $scope.auxItem.file.name = $scope.renameFilename;

                        if($scope.auxItem.isFile)
                            $scope.files[$scope.auxItem.key].name = $scope.renameFilename;
                        else
                            $scope.folders[$scope.auxItem.key].name = $scope.renameFilename;

                        $scope._showAlert('success', 'ok', ($scope.auxItem.isFile ? ' Il file' : 'La cartella') + " <b>"+ $scope.renameFilename + "</b> è "+ ($scope.auxItem.isFile ? 'stato rinominato' : 'stata rinominata') +" con successo!");

                    } else {
                        if(!response.data.is_logged) {
                            $scope._showAlert('warning', 'alert', "La sessione è scaduta. Effettuare di nuovo l'accesso al server");
                            $rootScope.logged = false;
                            $rootScope.username = null;
                            $location.path('/login');
                        
                        } else
                            $scope._showAlert('danger', 'remove', "Si è verificato un errore durante la rinominazione del"+ ($scope.auxItem.isFile ? ' file' : 'la cartella'));
                    }
                    
                    $scope.search = '';
                    $rootScope.loading = false;
                });
        }
    };

    $scope.move = function(){
        if(!$rootScope.loading){
            $rootScope.loading = true;
            $('#moveWindow').modal('hide');

            var realPathFrom = $scope.currentPaths.join('/');
            var realPathTo = $scope.moving.paths.join('/');

            // Check if the folders are different
            if(realPathFrom != realPathTo)
                $http.post('./ws/explore.php', 
                     {  cmd: 'move', 
                        pathFrom: realPathFrom,
                        pathTo: realPathTo,
                        filename: $scope.auxItem.file.name })
                        
                     .then(function(response) {
                        if(response.status == 200){
                                if(response.data.success){
                                    $scope.files.splice($scope.auxItem.key, 1);

                                    $scope._showAlert('success', 'ok', ($scope.auxItem.isFile ? ' Il file' : 'La cartella') + " <b>"+ $scope.auxItem.file.name + "</b> è "+ ($scope.auxItem.isFile ? 'stato spostato' : 'stata spostata') +" con successo!");
                                    $scope.auxItem = {};

                                } else  if (response.data.error == 'delete-failed'){
                                    $scope.files.splice($scope.auxItem.key, 1);

                                    $scope._showAlert('warning', 'alert', ($scope.auxItem.isFile ? ' Il file' : 'La cartella') + " <b>"+ $scope.auxItem.file.name + "</b> è "+ ($scope.auxItem.isFile ? 'stato spostato' : 'stata spostata') +" con successo, ma è rimast"+ ($scope.auxItem.isFile ? 'o' : 'a') +" una copia nella vecchia posizione");
                                    $scope.auxItem = {};
                                
                                } else 
                                    $scope._showAlert('danger', 'remove', "Si è verificato un errore durante lo spostamento del"+ ($scope.auxItem.isFile ? ' file' : 'la cartella'));

                        } else {
                            if(!response.data.is_logged) {
                                $scope._showAlert('warning', 'alert', "La sessione è scaduta. Effettuare di nuovo l'accesso al server");
                                $rootScope.logged = false;
                                $rootScope.username = null;
                                $location.path('/login');
                            
                            } else
                                $scope._showAlert('danger', 'remove', "Si è verificato un errore durante lo spostamento del"+ ($scope.auxItem.isFile ? ' file' : 'la cartella'));
                        }
                        
                        $scope.search = '';
                        $rootScope.loading = false;
                    });
        }
    };

    $scope.delete = function (){
        if(!$rootScope.loading){
            $rootScope.loading = true;
            $('#deleteWindow').modal('hide');

            var realPath = $scope.currentPaths.join('/');
            console.log( $scope.auxItem);
            // Get files and folders of root directory
            $http.post('./ws/explore.php', 
                 {  cmd: 'delete', 
                    path: realPath,
                    filename: $scope.auxItem.file.name })
                    
                 .then(function(response) {
                    if(response.status == 200 && response.data.success){
                        if($scope.auxItem.isFile)
                            $scope.files.splice($scope.auxItem.key, 1);
                        else
                            $scope.folders.splice($scope.auxItem.key, 1);
                        
                        $scope._showAlert('success', 'ok', ($scope.auxItem.isFile ? ' Il file' : 'La cartella') + " <b>"+ $scope.auxItem.file.name + "</b> è "+ ($scope.auxItem.isFile ? 'stato eliminato' : 'stata eliminata') +" con successo!");
                        $scope.auxItem = {};
                        
                    } else {
                        if(!response.data.is_logged) {
                            $scope._showAlert('warning', 'alert', "La sessione è scaduta. Effettuare di nuovo l'accesso al server");
                            $rootScope.logged = false;
                            $rootScope.username = null;
                            $location.path('/login');
                        
                        } else
                            $scope._showAlert('danger', 'remove', "Si è verificato un errore durante la cancellazione del"+ ($scope.auxItem.isFile ? ' file' : 'la cartella'));
                    }
                    
                    $scope.search = '';
                    $rootScope.loading = false;
                });
        }
    };

    $scope.download = function(){
        var realPath = $scope.currentPaths.join('/');

        window.location.href='./ws/explore.php?cmd=download&path='+ realPath +'&filename='+ $scope.auxItem.file.name;
    };

    $scope.doDefaultActionFile = function(key){
        $scope.auxItem = {
            'key': key,
            'file': $scope.files[key],
            'isFile' : false,
            'editable': $scope.files[key].ext != '' && $scope.files[key].ext in $scope.extensions && $scope.extensions[$scope.files[key].ext].editable 
        };    

        if($scope.auxItem.editable)
            $scope.openEditor();
        else
            $scope.download();
    };

    $scope.openMove = function(){ 
        $scope.moving.paths = $scope.currentPaths.slice(0);
        $scope.list('', true);
    };

    $scope.openCreate = function(isFile){
        $scope.auxItem = {
            'filename': isFile ? "nuovo file.txt" : "nuova cartella",
            'isFile' : isFile
        };
        
        $("#newFilename").select();
    };

    $scope.openContextMenu = function(event, key, isFile){
        event.preventDefault();

        $("#contextMenu")
            .show()
            .offset({ top: event.pageY, left: event.pageX});
        last = event.timeStamp;

        var isEditable = false;
        if(isFile){
            var ext = $scope.files[key].name.slice((($scope.files[key].name[0] != '.') ? Math.max(0, $scope.files[key].name.lastIndexOf(".")) || Infinity : 0) + 1);
            if(ext != '' && ext in $scope.extensions)
                isEditable = $scope.extensions[ext].editable;
        }
            
        $scope.auxItem = {
            'key': key,
            'file': isFile ? $scope.files[key] : $scope.folders[key],
            'isFile' : isFile,
            'editable': isEditable 
        };
        
        $scope.renameFilename = isFile ? $scope.files[key].name : $scope.folders[key].name;

        $(document).click(function(event) {
            var target = $(event.target);
                if (!target.is(".popover") && !target.parents().is(".popover")) {
                    if (last === event.timeStamp)
                        return;
                    $("#contextMenu").hide();

                return false;
            }
        });
    }
    
    $scope.openEditor = function(){ 
        if(!$rootScope.loading){
            $rootScope.loading = true;
            $scope.showEditor = true;

            var realPath = $scope.currentPaths.join('/');

            $http.post('./ws/explore.php', 
                 {  cmd: 'read', 
                    path: realPath,
                    filename: $scope.auxItem.file.name })
                    
                 .then(function(response) {
                    if(response.status == 200 && response.data.success){
                        var ext = $scope.auxItem.file.name.slice((($scope.auxItem.file.name[0] != '.') ? Math.max(0, $scope.auxItem.file.name.lastIndexOf(".")) || Infinity : 0) + 1);                    
                    
                        switch(ext){
                            case 'js':
                                $scope.editor.setOption('mode', 'javascript');
                                break;

                            case 'php':
                            case 'asp':
                                $scope.editor.setOption('mode', 'php');
                                break;
                                
                            case 'html':
                            case 'htm':
                                $scope.editor.setOption('mode', 'htmlmixed');
                                break;

                            case 'css':
                                $scope.editor.setOption('mode', 'css');
                                break;

                            case 'py':
                                $scope.editor.setOption('mode', 'python');
                                break;

                            case 'sql':
                                $scope.editor.setOption('mode', 'sql');
                                break;
                                
                            case 'xml':
                            case 'xsl':
                                $scope.editor.setOption('mode', 'xml');
                                break;

                            case 'c':
                            case 'cpp':
                            case 'cxx':
                            case 'java':
                                $scope.editor.setOption('mode', 'clike');
                                break;
                            
                            default:
                                $scope.editor.setOption('mode', '');
                            
                        }
                    
                        $scope.editor.setValue(response.data.buffer);

                        $(".CodeMirror").height($(window).height() - $(".navbar").outerHeight(true) - $("#editorTitle").outerHeight(true) - $("#editorCommand").outerHeight(true) - 15);
 
                    } else {
                        if(!response.data.is_logged) {
                            $scope._showAlert('warning', 'alert', "La sessione è scaduta. Effettuare di nuovo l'accesso al server");
                            $rootScope.logged = false;
                            $rootScope.username = null;
                            $location.path('/login');
                        
                        } else
                            $scope._showAlert('warning', 'alert', "E' accorso un errore la lettura del file");
                    }
                    $rootScope.loading = false;
                });
        }
    };

    // Upload File Callbacks
    $scope.ftpUploader.onBeforeUploadItem = function(item) {
        var realPath = $scope.currentPaths.join('/');
        Array.prototype.push.apply(item.formData, [{cmd: 'upload', path: realPath }]);
        $('#uploadingFileMenu').addClass('open');
    };            
            
    $scope.ftpUploader.onSuccessItem = function(item, response, status, headers) {
        if(response.success) {
            $scope.files.push({ name: item.file.name});
            item.remove();
            $scope.$apply();
        }
    };
    
    $scope.ftpUploader.onCompleteAll = function() {
        $scope.submitting = false;

        /*if($scope.uploadFilesErrors.length == 0)
            $scope._showAlert('success', 'ok', "I file sono stati caricati con successo!");

        else
            $scope._showAlert('danger', 'remove', "Si e' verificato un errore durante il caricamento dei file. Prego riprovare.");
            */
    };
    
    $http.post('./ws/profile.php', { cmd: 'is-logged' })
         .then(function(response) {
            if(response.status == 200 && response.data.success){
                $rootScope.logged = true;
                $scope.list('/');

            } else {
                $rootScope.logged = false;
                $rootScope.username = null;
                $location.path('/login');
            }
        });

}

function usersControll($scope, $rootScope, $http, $timeout, $location, FileUploader) { 
    $scope.Math =  window.Math;
    $scope.users = [];
    $scope.auxUser = {};
    
    // GUI
    $scope.mode = 'list';
    $scope.currentPage = 1;
    $scope.numOfEntry = 30;

    //Get the users list
    $http.post('./ws/users.php', 
            {   'cmd': 'list-users' })
        .then(function(response) {
            $scope.users =  response.data;
        });

   //Paging Functions
    $scope.setPage = function(n){ $scope.currentPage = n;}
    $scope.previousPage = function(){ if($scope.currentPage > 1) $scope.currentPage--; }
    $scope.nextPage = function(){ if($scope.currentPage < $scope.Math.ceil($scope.users.length/$scope.numOfEntry)) $scope.currentPage++;}

    $scope.updateFilteredUsers = function() { 
        $timeout(function() {
            $scope.currentPage = 1;
        }, 10);
    };

    $scope.saveUser = function() {
        $http.post('./ws/users.php', 
                {   'cmd':  ($scope.auxUser.id == null) ? 'add-user' : 'edit-user',
                    'id':       $scope.auxUser.id,
                    'username': $scope.auxUser.username,
                    'realname': $scope.auxUser.realname,
                    'login_type': $scope.auxUser.login_type,
                    'passwordNew': $scope.auxUser.passwordNew,
                    'passwordConfirm': $scope.auxUser.passwordConfirm,
                    'usertype':  $scope.auxUser.usertype
                })
            .then(function(response) {
                if(response.data.success){
                    $scope.auxUser.passwordNew = '';
                    $scope.auxUser.passwordConfirm = '';
                
                    if($scope.auxUser.id == null){
                        $scope.auxUser.id = response.data.id;
                        $scope.auxUser.title = $('#auxUserTitle option:selected').text();
                        $scope.users.unshift($scope.auxUser);

                        $scope.showAlert('success', 'check', "L'utente e' stato inserito con successo");
                        
                    } else
                        $scope.showAlert('success', 'check', "L'utente e' stato modificato con successo");

                    $scope.auxUser = {};
                    $scope.listMode = true;
                    $scope.editMode = false;
                    $scope.permissionMode = false;

                 } else {
                    if(response.data.error == 'confirm-not-match')
                        $scope.showAlert('warning', 'exclamation-triangle', "La password non coincide con la password di conferma");
                            
                    else if(response.data.error == 'password-too-short')
                        $scope.showAlert('warning', 'exclamation-triangle', "La password deve avere almeno 8 caratteri");

                    else if(response.data.error == 'username-alredy-taken')
                        $scope.showAlert('danger', 'times', "L'username scelto risulta già in uso. Prego sceglierne uno diverso");
                 }
            });
    };

    $scope.deleteUser = function(cancel) {
        if(cancel){
            $http.post('./ws/users.php', 
                    {   'cmd': 'delete-user',
                        'id':   $scope.auxUser.id
                    })
                .then(function(response) {
                    if(response.data.success){
                        $scope.users.splice($scope.users.indexOf($scope.auxUser), 1);
    
                        $scope.showAlert('success', 'check', "L'utente è stato eliminato con successo!");
                        $('#deleteWindow').modal('hide');
                     }      
                });
                
         } else 
            $('#deleteWindow').modal('hide');
    };


    // GUI
    $scope.backList = function(){
        $scope.mode = 'list';
    }

    $scope.check = function() {
        console.log($scope.userForm.$error);
    }

    $scope.addUser = function() {
        $scope.mode = 'edit';
        $scope.auxUser = {};
    };

    $scope.editUser = function(id) {
        $scope.mode = 'edit';
        $scope.auxUser = $scope.users.filter(function(item) {return item.id === id;})[0];
    };

    $scope.openDeleteUser = function(id){
        $scope.auxUser = $scope.users.filter(function(item) {return item.id === id;})[0];
    }
}

webRepoApp
    .filter('getIcon', function() {
        return function(file, $scope) {
            if(file.ext != '' && file.ext in $scope.extensions)
                return $scope.extensions[file.ext].icon;

            return 'file';
        }
    })
    .filter('iif', function () {
       return function(input, trueValue, falseValue) {
            return input ? trueValue : falseValue;
       };
    })
    .filter('trustAsHtml',function($sce){
        return function(input){
            return $sce.trustAsHtml(input);
        }
    })
    .directive('pwCheck', [function () {
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, ctrl) {
                var firstPassword = '#' + attrs.pwCheck;
                elem.on('keyup', function () {
                        scope.$apply(function () {
                            if (elem.val()==$(firstPassword).val() && elem.val().trim().length > 7){
                                $(firstPassword).parent()
                                    .addClass('has-success')
                                    .removeClass('has-error')
                                    .children()
                                        .addClass('glyphicon-ok')
                                        .removeClass('glyphicon-warning-sign')
                                        .removeClass('glyphicon-remove');

                                elem.parent()
                                    .addClass('has-success')
                                    .removeClass('has-error')
                                    .children()
                                        .addClass('glyphicon-ok')
                                        .removeClass('glyphicon-warning-sign')
                                        .removeClass('glyphicon-remove');
                                
                            } else {
                                $(firstPassword).parent()
                                    .addClass('has-error')
                                    .removeClass('has-success')
                                    .children()
                                        .addClass('glyphicon-remove')
                                        .removeClass('glyphicon-warning-sign')
                                        .removeClass('glyphicon-ok');

                                elem.parent()
                                    .addClass('has-error')
                                    .removeClass('has-success')
                                    .children()
                                        .addClass('glyphicon-remove')
                                        .removeClass('glyphicon-warning-sign')
                                        .removeClass('glyphicon-ok');
                            }
                        });
                    });
            }
        }
    }])
    .directive('ngRightClick', function($parse) {
        return function(scope, element, attrs) {
            var fn = $parse(attrs.ngRightClick);
            element.bind('contextmenu', function(event) {
                scope.$apply(function() {
                    event.preventDefault();
                    fn(scope, {$event:event});
                });
            });
        };
    })
    .filter('range', function() {
        return function(input, min, max) {
            min = parseInt(min); //Make string input int
            max = parseInt(max);
            for (var i=min; i<=max; i++)
                input.push(i);
            return input;
        };
    })
    .filter('startFrom', function() {
        return function(input, start) {
            if(input && Array.isArray(input)) {
                start = +start; //parse to int
                return input.slice(start);
            }
            return [];
        }
    });

