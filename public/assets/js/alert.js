function showAlert(type,mesage, title) {
    Swal.fire({
        icon: type,
        title: title,
        text: mesage,
        timer: 4000,
        showConfirmButton: type !== 'success'
    });
}