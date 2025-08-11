@extends('app')

@section('title', 'Pedidos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Pedidos</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal" data-mode="create"
            data-action="{{ route('orders.store') }}">
            + Novo Pedido
        </button>
    </div>

    <div class="card">
        <div class="card-header">Lista de pedidos</div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="ordersTable" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Pedido</th>
                            <th>Entrega</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $o)
                            <tr id="order-row-{{ $o->id }}">
                                <td>{{ $o->id }}</td>
                                <td>{{ $o->customer_name }}</td>
                                <td>{{ optional($o->order_date)->format('d/m/Y') }}</td>
                                <td>{{ optional($o->delivery_date)->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    @php
                                        $badgeClass =
                                            $o->status === 'entregue'
                                                ? 'success'
                                                : ($o->status === 'cancelado'
                                                    ? 'danger'
                                                    : 'secondary');
                                    @endphp
                                    <span class="badge text-bg-{{ $badgeClass }}">{{ ucfirst($o->status) }}</span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary js-edit" data-bs-toggle="modal"
                                        data-bs-target="#orderModal" data-mode="edit"
                                        data-action="{{ route('orders.update', $o) }}" data-id="{{ $o->id }}"
                                        data-customer="{{ e($o->customer_name) }}" data-status="{{ $o->status }}"
                                        data-order_date="{{ optional($o->order_date)->toDateString() }}"
                                        data-delivery_date="{{ optional($o->delivery_date)->toDateString() }}">Editar</button>

                                    <form action="{{ route('orders.destroy', $o) }}" method="POST"
                                        class="d-inline js-delete">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Apagar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal único (criar/editar) --}}
    <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="orderForm" method="POST" class="modal-content needs-validation" novalidate>
                @csrf
                <div class="modal-header mt-3 mx-3">
                    <h5 class="modal-title" id="orderModalTitle">Novo pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body mx-3">
                    @include('orders._form')
                </div>
                <div class="modal-footer m-3">
                    <button class="btn btn-primary" id="orderSubmitBtn">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
  @vite('resources/js/orders/orders.js')
@endpush

