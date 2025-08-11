import $ from 'jquery';
import 'datatables.net-bs5';
import Swal from 'sweetalert2';

function capitalizar(s) { return (s || '').charAt(0).toUpperCase() + (s || '').slice(1); };
function escaparHtml(texto) { return $('<div/>').text(texto ?? '').html(); };

const templateAtualizar = $('meta[name="orders-update-template"]').attr('content') || '';
const templateExcluir = $('meta[name="orders-destroy-template"]').attr('content') || '';
const urlAtualizar = (id) => templateAtualizar.replace(/0$/, id);
const urlExcluir = (id) => templateExcluir.replace(/0$/, id);

$(function () {
    const tabela = $('#ordersTable').DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json' }
    });

    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    $('#orderModal').on('show.bs.modal', function (ev) {
        const botao = $(ev.relatedTarget);
        if (!botao.length) return;

        const modo = botao.data('mode') || 'create';
        const formulario = $('#orderForm');
        const titulo = $('#orderModalTitle');

        const cliente = formulario.find('[name="customer_name"]');
        const status = formulario.find('[name="status"]');
        const dataPedido = formulario.find('[name="order_date"]');
        const dataEntrega = formulario.find('[name="delivery_date"]');

        formulario.find('input[name="_method"]').remove();

        if (modo === 'create') {
            titulo.text('Novo pedido');
            formulario.attr('action', botao.data('action'));
            cliente.val('');
            status.val('pendente');
            dataPedido.val(new Date().toISOString().slice(0, 10));
            dataEntrega.val('');
        } else {
            titulo.text('Editar pedido');
            formulario.attr('action', botao.data('action'));
            $('<input>', { type: 'hidden', name: '_method', value: 'PUT' }).appendTo(formulario);

            cliente.val(botao.data('customer') || '');
            status.val(botao.data('status') || 'pendente');
            dataPedido.val(botao.data('order_date') || '');
            dataEntrega.val(botao.data('delivery_date') || '');
        }
    });

    $('#orderForm').on('submit', function (e) {
        e.preventDefault();
        const formulario = $(this);

        $.ajax({
            url: formulario.attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            data: formulario.serialize(),
            success: (dados) => {
                if (!dados || dados.success !== true || dados.id == null) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ops!',
                        text: dados?.message || 'Ocorreu um erro ao processar sua solicitação.'
                    });
                    return false;
                };

                const seletorLinha = '#order-row-' + dados.id;
                const classeBadge = dados.status === 'entregue' ? 'success' : (dados.status === 'cancelado' ? 'danger' : 'secondary');

                if ($(seletorLinha).length) {
                    const linha = $(seletorLinha);
                    linha.find('td').eq(1).text(dados.customer_name);
                    linha.find('td').eq(2).text(dados.order_date_formatted || '');
                    linha.find('td').eq(3).text(dados.delivery_date_formatted || '-');
                    linha.find('td').eq(4).html(`<span class="badge text-bg-${classeBadge}">${capitalizar(dados.status)}</span>`);

                    const botaoEditar = linha.find('button.js-edit');
                    botaoEditar
                        .attr('data-customer', dados.customer_name)
                        .attr('data-status', dados.status)
                        .attr('data-order_date', dados.order_date || '')
                        .attr('data-delivery_date', dados.delivery_date || '');

                    tabela.row(linha).invalidate().draw(false);
                } else {
                    const acoesHtml = `
            <button class="btn btn-sm btn-outline-primary js-edit"
                    data-bs-toggle="modal" data-bs-target="#orderModal"
                    data-mode="edit"
                    data-action="${urlAtualizar(dados.id)}"
                    data-id="${dados.id}"
                    data-customer="${escaparHtml(dados.customer_name)}"
                    data-status="${dados.status}"
                    data-order_date="${dados.order_date || ''}"
                    data-delivery_date="${dados.delivery_date || ''}">Editar</button>
            <form action="${urlExcluir(dados.id)}" method="POST" class="d-inline js-delete">
              <input type="hidden" name="_token" value="${csrfToken}">
              <input type="hidden" name="_method" value="DELETE">
              <button class="btn btn-sm btn-outline-danger">Apagar</button>
            </form>`;

                    const nodo = tabela.row.add([
                        dados.id,
                        escaparHtml(dados.customer_name),
                        dados.order_date_formatted || '',
                        dados.delivery_date_formatted || '-',
                        `<span class="badge text-bg-${classeBadge}">${capitalizar(dados.status)}</span>`,
                        acoesHtml
                    ]).draw(false).node();
                    $(nodo).attr('id', 'order-row-' + dados.id);
                };

                Swal.fire({
                    icon: 'success',
                    title: dados.msg,
                    timer: 1400,
                    showConfirmButton: false,
                    didClose: () => {
                        $('#orderModal').find('.btn-close').trigger('click');
                    }
                });
            },
            error: (xhr) => {
                const msg = xhr.responseJSON?.message || (xhr.status === 422 ? 'Dados inválidos.' : 'Erro ao salvar.');
                Swal.fire({
                    icon: 'error',
                    title: 'Ops!',
                    text: msg
                });
            }
        });
    });

    $(document).on('submit', '.js-delete', function (e) {
        e.preventDefault();
        const formulario = $(this);
        Swal.fire({
            title: 'Confirma apagar?',
            text: 'Essa ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, apagar',
            cancelButtonText: 'Cancelar'
        }).then((res) => {
            if (!res.isConfirmed) return;
            $.ajax({
                url: formulario.attr('action'),
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                data: formulario.serialize(),
                success: (resp) => {
                    if (!resp || resp.success !== true) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ops!',
                            text: resp?.message || 'Ocorreu um erro ao processar sua solicitação.'
                        });
                        return false;
                    };

                    const linha = formulario.closest('tr');
                    tabela.row(linha).remove().draw(false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Pedido apagado com sucesso!',
                        timer: 1200,
                        showConfirmButton: false
                    });
                },
                error: (xhr) => {
                    const msg = xhr.responseJSON?.message || (xhr.status === 422 ? 'Dados inválidos.' : 'Erro ao apagar seu pedido');
                    Swal.fire({
                        icon: 'error',
                        title: 'Ops!',
                        text: msg
                    });
                }
            });
        });
    });
});
