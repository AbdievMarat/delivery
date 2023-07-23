<table class="table">
    <thead>
    <tr>
        <th scope="col" style="width: 130px;">Дата</th>
        <th scope="col" style="width: 170px;">Пользователь</th>
        <th scope="col">Сообщение</th>
    </tr>
    </thead>
    <tbody>
    @foreach($logs as $log)
        <tr>
            <td>{{ date('d.m.Y H:i', strtotime($log->created_at)) }}</td>
            <td>{{ $log->user_name }}</td>
            <td>{{ $log->message }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
