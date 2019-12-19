var gfWebRepoApp = angular.module('gfWebRepo', ['ngRoute', 'angularFileUpload']); //, 'ngMaterial', , 

gfWebRepoApp.config(function($httpProvider, $routeProvider, $locationProvider) {
	$routeProvider
		.when('/login', {
				templateUrl: './views/login.html',
				controller: loginControll
			})
		.when('/profile', {
				templateUrl: './views/profile.html',
				controller: profileControll
			})
		.when('/explore', { //  /:path
				templateUrl: './views/explore.html',
				controller: exploreControll
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

/** CONTROLLERS **/
gfWebRepoApp.controller('navControll', function($scope, $rootScope, $http, $location, $sce, FileUploader) { 
    $rootScope.alertWindow = {type:'', message: '', visible: false};
    $rootScope.logged = false;
    $rootScope.ftpUploader = new FileUploader({ url: 'ws/explore.php', alias: 'upload'});

    $scope.doLogout = function() {
        $http.post('./ws/users.php', { cmd: 'logout' })
             .then(function(response) {
                if(response.status == 200 && response.data.success){
                    $rootScope.logged = false;
                    $location.path('/login');
                
                }
            });
    };
    
    //Tools
    $rootScope._showAlert = function(typeMessage, icon, messageText) {
        $rootScope.alertWindow = {type:'alert-'+ typeMessage, icon: icon, message: messageText, visible: true};

        $('#alertWindow')
            .slideDown("slow", "easeOutBounce")
            .delay(5000)
            .slideUp("slow", "easeOutBounce", function(){ $rootScope.alertWindow.visible = false; });
    };
});

function loginControll($scope, $rootScope, $http, $route, $routeParams, $location, $filter) {
    $scope.username = '';
    $scope.password = '';
    $rootScope.logged = false;

    $http.post('./ws/users.php', { cmd: 'is-logged' })
         .then(function(response) {
            if(response.status == 200 && response.data.success){
                $rootScope.logged = true;
                $location.path('/explore');
            }
        });
    
    $scope.doLogin = function() {
        $http.post('./ws/users.php', 
             {  cmd: 'login', 
                username: $scope.username, 
                password: $scope.password })
                
             .then(function(response) {
                if(response.status == 200 && response.data.success){
                    $rootScope.logged = true;
                    $location.path('/explore');
                
                } else {
                    $scope._showAlert('danger', 'remove', "Nome utente o password non validi.");
                }
            });
    };
}

function profileControll($scope, $http, $timeout) {
    $scope.profile = Array();

    //Get the user profile
    $http.post('./ws/users.php', 
            {   'cmd': 'get-profile' })
        .then(function(response) {
            $scope.profile =  response.data;
        });

    $scope.editProfile = function() {
        $http.post('./ws/users.php', 
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

    $scope.loading = false;
    $scope.loadingMove = false;
    $scope.loading = false;
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
        'php':      { icon : 'file-code-o', editable: true },
        'html':     { icon : 'file-code-o', editable: true },
        'htm':      { icon : 'file-code-o', editable: true },
        'htaccess': { icon : 'file-code-o', editable: true },
        'css':      { icon : 'file-code-o', editable: true },
        'js':       { icon : 'file-code-o', editable: true },
        'py':       { icon : 'file-code-o', editable: true },
        'asp':      { icon : 'file-code-o', editable: true },
        'sql':      { icon : 'file-code-o', editable: true },
        'xml':      { icon : 'file-code-o', editable: true },
        'xsl':      { icon : 'file-code-o', editable: true },
        'c':        { icon : 'file-code-o', editable: true },
        'cpp':      { icon : 'file-code-o', editable: true },
        'cxx':      { icon : 'file-code-o', editable: true },
        'java':     { icon : 'file-code-o', editable: true },
        'txt':      { icon : 'file-text-o', editable: true },
        'ini':      { icon : 'file-text-o', editable: true },
        'doc':      { icon : 'file-word-o', editable: false },
        'docx':     { icon : 'file-word-o', editable: false },
        'odt':      { icon : 'file-word-o', editable: false },
        'xls':      { icon : 'file-excel-o', editable: false },
        'xlsx':     { icon : 'file-excel-o', editable: false },
        'ods':      { icon : 'file-excel-o', editable: false },
        'ppt':      { icon : 'file-powerpoint-o', editable: false },
        'pptx':     { icon : 'file-powerpoint-o', editable: false },
        'odp':      { icon : 'file-powerpoint-o', editable: false },
        'jpg':      { icon : 'file-image-o', editable: false },
        'png':      { icon : 'file-image-o', editable: false },
        'gif':      { icon : 'file-image-o', editable: false },
        'bmp':      { icon : 'file-image-o', editable: false },
        'tiff':     { icon : 'file-image-o', editable: false },
        'zip':      { icon : 'file-archive-o', editable: false },
        'rar':      { icon : 'file-archive-o', editable: false },
        'gzip':     { icon : 'file-archive-o', editable: false },
        '7z':       { icon : 'file-archive-o', editable: false },
        'gz':       { icon : 'file-archive-o', editable: false },
        'xz':       { icon : 'file-archive-o', editable: false },
        'bz2':      { icon : 'file-archive-o', editable: false }
    }

    $scope.list = function (path, isMoveFile){
        if(typeof isMoveFile == "undefined")
            isMoveFile = false;

        if((!$scope.loading && !isMoveFile) || (!$scope.loading && isMoveFile)){
            if(isMoveFile)
                $scope.loadingMove = true
            else
                $scope.loading = true;

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
                            
                        $scope._showAlert('warning', 'alert', "E' accorso un errore durante la comunicazione con il server");
                        $location.path('/login');
                    }
                    $scope.search = '';
                    if(isMoveFile)
                        $scope.loadingMove = false
                    else
                        $scope.loading = false;

                });
        }
    };

    $scope.listDirFromIndex = function (index, isMoveFile){
        if(typeof isMoveFile == "undefined")
            isMoveFile = false;

        if((!$scope.loading && !isMoveFile) || (!$scope.loading && isMoveFile)){
            if(isMoveFile)
                $scope.loadingMove = true
            else
                $scope.loading = true;

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
                        $scope._showAlert('warning', 'alert', "E' accorso un errore durante la comunicazione con il server");
                    }
                    $scope.search = '';
                    if(isMoveFile)
                        $scope.loadingMove = false
                    else
                        $scope.loading = false;

                });
        }
    }

    $scope.saveFile = function(){
        if(!$scope.loading){
            $scope.loading = true;

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
                        
                    } else
                        $scope._showAlert('danger', 'remove', "Si è verificato un errore durante il salvataggio del file");

                    $scope.loading = false;
                });
        }
    };

    $scope.create = function(){
        if(!$scope.loading){
            $scope.loading = true;
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
                        
                    } else
                        $scope._showAlert('danger', 'remove', "Si è verificato un errore durante la creazione del"+ ($scope.auxItem.isFile ? ' file' : 'la cartella'));

                    $scope.search = '';
                    $scope.loading = false;
                });
        }
    };

    $scope.rename = function(){
        if(!$scope.loading){
            $scope.loading = true;
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

                    } else
                        $scope._showAlert('danger', 'remove', "Si è verificato un errore durante la rinominazione del"+ ($scope.auxItem.isFile ? ' file' : 'la cartella'));

                    $scope.search = '';
                    $scope.loading = false;
                });
        }
    };

    $scope.move = function(){
        if(!$scope.loading){
            $scope.loading = true;
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

                        } else
                            $scope._showAlert('danger', 'remove', "Si è verificato un errore durante lo spostamento del"+ ($scope.auxItem.isFile ? ' file' : 'la cartella'));

                        $scope.search = '';
                        $scope.loading = false;
                    });
        }
    };

    $scope.delete = function (){
        if(!$scope.loading){
            $scope.loading = true;
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
                        
                    } else
                        $scope._showAlert('danger', 'remove', "Si è verificato un errore durante la cancellazione del"+ ($scope.auxItem.isFile ? ' file' : 'la cartella'));

                    $scope.search = '';
                    $scope.loading = false;
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
        if(!$scope.loading){
            $scope.loading = true;
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
                        $scope._showAlert('warning', 'alert', "E' accorso un errore la lettura del file");
                    }
                    $scope.loading = false;
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
            item.remove();
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
    
    $http.post('./ws/users.php', { cmd: 'is-logged' })
         .then(function(response) {
            if(response.status == 200 && response.data.success){
                $rootScope.logged = true;
                $scope.list('/');

            } else {
                $rootScope.logged = false;
                $location.path('/login');
            }
        });

}

gfWebRepoApp
.filter('getIcon', function() {
    return function(file, $scope) {
        if(file.ext != '' && file.ext in $scope.extensions)
            return $scope.extensions[file.ext].icon;

        return 'file-o';
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
});

