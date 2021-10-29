let form = document.querySelector('#form');
if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let data = new FormData(form);
        data.append('action', 'unload_func');
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: data,
        }).then(response => {
            if (response.status !== 200) {
                return Promise.reject();
            }
            return response.text();
        }).then(answer => {
            console.log(answer);
        }).catch(() => console.log('error'));
    });
}