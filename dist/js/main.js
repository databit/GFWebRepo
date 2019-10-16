var gfWebRepoApp = angular.module('gfWebRepo', ['ngRoute', 'angularFileUpload']); //, 'ngMaterial', , 'angular.filter'

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

	//Enable cross domain calls
	$httpProvider.defaults.useXDomain = true;

    // Use x-www-form-urlencoded Content-Type
    //$httpProvider.defaults.headers.post['Content-Type'] = 'application/json'; //'application/x-www-form-urlencoded;charset=utf-8';

	//Remove the header used to identify ajax call  that would prevent CORS from working
	delete $httpProvider.defaults.headers.common['X-Requested-With'];
	
	//$locationProvider.html5Mode(true);
})

/*
gfWebRepoApp.config(function($mdDateLocaleProvider) {
    //$mdDateLocaleProvider.formatDate = function(date) {
    $mdDateLocaleProvider.months = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
    $mdDateLocaleProvider.shortMonths = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
    $mdDateLocaleProvider.days = ['domenica', 'lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato'];
    $mdDateLocaleProvider.shortDays = ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'];

    // Can change week display to start on Monday.
    $mdDateLocaleProvider.firstDayOfWeek = 1;

    $mdDateLocaleProvider.formatDate = function(date) {
        return moment(date).format('DD-MM-YYYY');
    };
});

/** CONTROLLERS **/
gfWebRepoApp.controller('navControll', function($scope, $rootScope, $http, $timeout) { 
    $rootScope.alertWindow = {type:'', message: '', visible: false};
    $rootScope.logged = false;
    
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
/*
    $scope.$on("$routeChangeStart", function(event, next, current) {
        if(next.$$route)
            switch(next.$$route.originalPath){
                case "/classes/:noReload":
                    event.preventDefault();
                    $scope.classStub = {};
                    $scope.mainPage = true;
                    break;

                case "/add-address":
                    event.preventDefault();
                    $scope.mainPage = false;
                    $scope.partialTemplate = next.templateUrl;
                    break;

                case "/add-section/:address":
                    event.preventDefault();
                    $scope.mainPage = false;
                    $scope.partialTemplate = next.templateUrl;

                    $scope.classStub.address = next.params.address;
                    break;
            
                case "/add-class/:address/:section/:rank":
                    event.preventDefault();
                    $scope.mainPage = false;
                    $scope.partialTemplate = next.templateUrl;

                    $scope.classStub.address = next.params.address;
                    $scope.classStub.section = next.params.section;
                    $scope.classStub.rank = next.params.rank;
                    $scope.classStub.description = "";
                    break;

                case "/classes/edit/:id":
                    event.preventDefault();
                    $scope.mainPage = false;
                    $scope.partialTemplate = next.templateUrl;

                    var found = $filter('filter')($scope.classes, {id: next.params.id}, true);
                    
                    if (found.length) {
                        $scope.classStub = angular.fromJson(found[0]);
                        
                    } else {
                        $scope.classStub = {};
                        $scope.mainPage = true;
                    }
                    break;
            }
    });

    $scope.sortClassesBySection = function (classes, rank) {
        var classesBySection = {};
        console.log("Do sort");
        for(key in classes)
            classesBySection[classes[key].rank-1] = classes[key];

        for(var i=0; i< rank; i++)
            if(!classesBySection.hasOwnProperty(i))
                classesBySection[i] = {"id": -1, "address": "", "rank": i+1, "section": "", "description": "", "students": 0};
        
        return classesBySection;
    };

    $scope.addAddress = function() {
        $location.path('/add-section/'+ $scope.classStub.address);
    };

    $scope.addSection = function() {
        $scope.classes.push({"id": -1, "address": $scope.classStub.address, "rank": 1, "section": $scope.classStub.section, "description": "", "students": 0});
        $location.path('/classes/1')
    };

	$scope.editClass = function() {
        $http.post(ABSOLUTE_PATH + 'components/DigitalSchool/ws/school.php', 
                {   cmd: 'edit-class', 
                    id: $scope.classStub.id, 
                    description: $scope.classStub.description,
                    secureTicket: ST_SCHOOL })
                    
             .then(function(response) {
                if(response.status == 200)
                    for (key in $scope.classes)
                        if($scope.classes[key].id == $scope.classStub.id) {
                            $scope.classes[key].description = ""+ $scope.classStub.description;
                            $scope.classStub = {};
                            $scope.mainPage = true;
                            break;
                        }
            });
	};

    $scope.deleteClass = function() {
        $http.post(ABSOLUTE_PATH + 'components/DigitalSchool/ws/school.php', {cmd: 'delete-class', 'id': $scope.classStub.id })
            .then(function(response) {
                if(response.status == 200)
                    $route.reload();
            });
	};*/
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
                    'realname': $scope.profile.realname,
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

function exploreControll($scope, $http, $timeout, FileUploader) { 
    $scope.currentPath = '/';
    $scope.ftpUploader = new FileUploader({ url: 'ws/explore.php', alias: 'upload' });
    $scope.uploadFilesErrors;
    
    $scope.files = {};
    $scope.folders = {};

    // Get files and folders of root directory
    $http.post('./ws/explore.php', 
         {  cmd: 'list', 
            path: '/' })
            
         .then(function(response) {
            if(response.status == 200 && response.data.success){
                $scope.folders = response.data.success;
                $scope.files = response.data.files;
                
            } else {
                $scope.files = {};
                $scope.folders = {};
                $scope._showAlert('warning', 'alert', "E' accorso un errore durante la comunicazione con il server");
            }
        });


    $scope.saveCircular = function (){
        $scope.submitting = true;

        // Upload the attachments
        if($scope.ftpUploader.queue.length > 0){
            $scope.uploadFilesErrors = [];
            $scope.ftpUploader.uploadAll();

        } 
    };

    // Upload File Callbacks
    $scope.ftpUploader.onBeforeUploadItem = function(item) {
        Array.prototype.push.apply(item.formData, [{cmd: 'upload', path: $scope.currentPath }]);
    };            
            
    $scope.ftpUploader.onSuccessItem = function(item, response, status, headers) {
        if(response.success) {
            item.remove();
        }
    };
    
    $scope.ftpUploader.onCompleteAll = function() {
        $scope.submitting = false;

        if($scope.uploadFilesErrors.length == 0)
            $scope._showAlert('success', 'ok', "I file sono stati caricati con successo!");

        else
            $scope._showAlert('danger', 'remove', "Si e' verificato un errore durante il caricamento dei file. Prego riprovare.");
    };
    


}

gfWebRepoApp.directive('pwCheck', [function () {
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
}]);


