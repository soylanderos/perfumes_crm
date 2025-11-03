$(function () {
    const login_controller = 'modules/login/controller/login_controller.php';
    // Handle form submission for login
    $(document).on('click', '#btn_login', function(e) {
        e.preventDefault();
        var user_request = 'verify_login';
        var email = $('#email').val();
        var password = $('#password').val();

        // Submit login request
        $.post(login_controller, {
            user_request: user_request,
            email: email,
            password: password,
        }, function(data) {
            var response = JSON.parse(data);
            if(response.status === 'success') {
                $('#message_container').html(response.view);
                Swal.fire({
                    icon: 'success',
                    title: 'Success...',
                    text: response.message
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: response.message
                });
            }
        });
    });
});
