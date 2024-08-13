/**
 * Novo SGA - Admin
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
var App = App || {};

App.Admin = {
    reiniciarSenhas(alert) {
        if (confirm(alert)) {
            App.ajax({
                url: App.url('acumular_atendimentos'),
                type: 'post',
                success() {
                    new bootstrap.Modal("#dialog-reiniciar").show();
                }
            });
        }
        return false;
    },
    limparSenhas(alert) {
        if (confirm(alert)) {
            App.ajax({
                url: App.url('limpar_atendimentos'),
                type: 'post',
                success() {
                    new bootstrap.Modal("#dialog-limpar").show();
                }
            });
        }
        return false;
    },
};

[...document.querySelectorAll('[type=submit][value=DELETE]')].forEach((e) => {
    e.addEventListener('click', () => {
        return confirm('Você realmente deseja remover?');
    })
});
