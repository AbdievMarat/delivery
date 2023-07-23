$(() => {
    const channel = pusher.subscribe('liveOrders');
    channel.bind('order-list-update', function() {
        location.reload();
    });
});
