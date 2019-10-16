var profileApp = angular.module('profile', ['filters']);
profileApp.controller('profileControl', function($scope, $http, $timeout ) {
    $scope.profile = Array();

    //Get the user profile
    $http.post(ABSOLUTE_PATH + 'components/SchoolSite/ws/profile.php', 
            {   'secureTicket': ST_WS_PROFILE,
                'cmd': 'get-profile' })
        .then(function(response) {
            $scope.profile =  response.data;
        });

    $scope.editProfile = function() {
        $http.post(ABSOLUTE_PATH + 'components/SchoolSite/ws/profile.php', 
                {   'secureTicket': ST_WS_PROFILE,
                    'cmd': 'edit-profile',
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
    
    //Tools
    $scope._showAlert = function(typeMessage, icon, messageText) {
        $scope.alertWindow = {type:'alert-'+ typeMessage, icon: icon, message: messageText, visible: true};

        $('#alertWindow')
            .slideDown("slow", "easeOutBounce")
            .delay(5000)
            .slideUp("slow", "easeOutBounce", function(){ $scope.alertWindow.visible = false; });
    };

});

profileApp.directive('pwCheck', [function () {
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
