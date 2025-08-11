{{-- Frmulário de Pedido (usado para criar e editar) --}}
<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Cliente</label>
    <input name="customer_name" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
      @foreach(['pendente','entregue','cancelado'] as $st)
        <option value="{{ $st }}">{{ ucfirst($st) }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label">Data do pedido</label>
    <input type="date" name="order_date" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Data da entrega</label>
    <input type="date" name="delivery_date" class="form-control">
  </div>
</div>
