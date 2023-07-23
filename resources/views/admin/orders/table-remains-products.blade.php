<div class="alert alert-success alert-dismissible pe-3 fade show" role="alert">
    <p class="text-center">
        Доступные товары в <strong>{{ $shop_name }}</strong> <br>
        на {{ $date_withdrawal_remains }}
    </p>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

    <table class="table table-remains-products">
        <thead>
        <tr>
            <th scope="col">Наименование</th>
            <th scope="col" style="min-width: 50px;">К-во</th>
            <th scope="col">Доступно</th>
        </tr>
        </thead>
        <tbody>
        @foreach($remainsProducts as $product)
            <tr @if($product['quantity'] > $product['remainder']) class="table-danger" @endif>
                <td>{{ $product['product_name'] }}</td>
                <td>{{ $product['quantity'] }}</td>
                <td>{{ $product['remainder'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
