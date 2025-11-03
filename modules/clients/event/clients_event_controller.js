const clients_controller = "modules/clients/controller/clients_controller.php";

function show_loader() {
    $("#loading-spinner").removeClass("d-none");
}
function hide_loader() {
    $("#loading-spinner").addClass("d-none");
}

function get_current_time() {
    var now = new Date();
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var seconds = String(now.getSeconds()).padStart(2, '0');
    var new_time = hours + ':' + minutes + ':' + seconds;

    return new_time;
}

function get_current_date() {
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var new_date = year + '-' + month + '-' + day;

    return new_date;
}

function errorMessage(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
    });
    console.error("Error:", message);
}

$(document).on('click', '#btn_add_new_client', function(e) {
    e.preventDefault();
    let user_request = 'fetch_add_client_form';

    $.ajax({
        url: clients_controller,
        type: 'POST',
        data: { user_request: user_request },
        beforeSend: function() {
            show_loader();
        },
        success: function(response) {
            response = JSON.parse(response);
            if (response.status === 'success') {
                $('#modal_container').html(response.view);
                $('#client_modal').modal('show');
            } else {
                errorMessage(response.message);
            }
            hide_loader();
        },
        error: function(xhr, status, error) {
            hide_loader();
            console.error("Error:", error);
        }
    });
});

$(document).on('submit', '#form_customer', function(e) {
    e.preventDefault();
    let form = $(this)[0];
    let formData = new FormData(form);

    //Validation form
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }

    $.ajax({
        url: clients_controller,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            show_loader();
        },
        success: function(response) {
            response = JSON.parse(response);
            if (response.status === 'success') {
                $('#client_modal').modal('hide');
            } else {
                errorMessage(response.message);
            }
            hide_loader();
        },
        error: function(xhr, status, error) {
            hide_loader();
            console.error("Error:", error);
        }
    });
});


$(document).on('hidden.bs.modal', '#client_modal', function () {
    $('#modal_container').empty();
    $('.modal-backdrop').remove();
});

$(document).on('click', '.client-card', function(e) {
    e.preventDefault();
    let customer_id = $(this).data('customer-id');
    let user_request = 'fetch_client_details';

    $.ajax({
        url: clients_controller,
        type: 'POST',
        data: { user_request: user_request, customer_id: customer_id },
        beforeSend: function() {
            show_loader();
        },
        success: function(response) {
            response = JSON.parse(response);
            if (response.status === 'success') {
                $('#modal_container').html(response.view);
                $('#client_modal').modal('show');
            } else {
                errorMessage(response.message);
            }
            hide_loader();
        },
        error: function(xhr, status, error) {
            hide_loader();
            console.error("Error:", error);
        }
    });

});