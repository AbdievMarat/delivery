<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Order\AcceptOrderYandexAction;
use App\Actions\Admin\Order\CancelInfoOrderYandexAction;
use App\Actions\Admin\Order\CancelOrderYandexAction;
use App\Actions\Admin\Order\CancelMobileApplicationPaidOrderAction;
use App\Actions\Admin\Order\CancelOtherPaidOrderAction;
use App\Actions\Admin\Order\CancelUnpaidOrderAction;
use App\Actions\Admin\Order\CreateAction;
use App\Actions\Admin\Order\EditAction;
use App\Actions\Admin\Order\GetDriverPositionYandex;
use App\Actions\Admin\Order\GetOptimalOrderInYandexAction;
use App\Actions\Admin\Order\GetOrdersInYandexAction;
use App\Actions\Admin\Order\GetRemainsProductsAction;
use App\Actions\Admin\Order\GetShopsOfCountryAction;
use App\Actions\Admin\Order\IndexAction;
use App\Actions\Admin\Order\LiveOrdersAction;
use App\Actions\Admin\Order\ProductSearchAction;
use App\Actions\Admin\Order\RestorePaidOrderAction;
use App\Actions\Admin\Order\ShowAction;
use App\Actions\Admin\Order\StoreAction;
use App\Actions\Admin\Order\StoreOrderYandexAction;
use App\Actions\Admin\Order\UpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\StoreOrderYandexRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Order;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:update,order')->only('edit', 'update');
        $this->middleware('can:cancelUnpaid,order')->only('cancelUnpaidOrder');
        $this->middleware('can:restorePaid,order')->only('restorePaidOrder');
        $this->middleware('can:cancelMobileApplicationPaid,order')->only('cancelMobileApplicationPaidOrder');
        $this->middleware('can:cancelOtherPaid,order')->only('cancelOtherPaidOrder');
    }

    /**
     * @param IndexAction $action
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(IndexAction $action): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return $action();
    }

    /**
     * @param CreateAction $action
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function create(CreateAction $action): Application|View|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return $action();
    }

    /**
     * @param StoreOrderRequest $request
     * @param StoreAction $action
     * @return RedirectResponse
     * @throws GuzzleException
     */
    public function store(StoreOrderRequest $request, StoreAction $action): RedirectResponse
    {
        return $action($request);
    }

    /**
     * @param Order $order
     * @param ShowAction $action
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function show(Order $order, ShowAction $action): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return $action($order);
    }

    /**
     * @param Order $order
     * @param EditAction $action
     * @return Factory|View|Application|\Illuminate\Contracts\Foundation\Application
     */
    public function edit(Order $order, EditAction $action): Factory|View|Application|\Illuminate\Contracts\Foundation\Application
    {
        return $action($order);
    }

    /**
     * @param UpdateOrderRequest $request
     * @param Order $order
     * @param UpdateAction $action
     * @return RedirectResponse
     */
    public function update(UpdateOrderRequest $request, Order $order, UpdateAction $action): RedirectResponse
    {
        return $action($request, $order);
    }

    /**
     * @param Request $request
     * @param ProductSearchAction $action
     * @return JsonResponse
     */
    public function productSearch(Request $request, ProductSearchAction $action): JsonResponse
    {
        return $action($request);
    }

    /**
     * @param Request $request
     * @param GetRemainsProductsAction $action
     * @return JsonResponse
     */
    public function getRemainsProducts(Request $request, GetRemainsProductsAction $action): JsonResponse
    {
        return $action($request);
    }

    /**
     * @param Request $request
     * @param GetShopsOfCountryAction $action
     * @return JsonResponse
     */
    public function getShopsOfCountry(Request $request, GetShopsOfCountryAction $action): JsonResponse
    {
        return $action($request);
    }

    /**
     * @param StoreOrderYandexRequest $request
     * @param StoreOrderYandexAction $action
     * @return JsonResponse
     */
    public function storeOrderYandex(StoreOrderYandexRequest $request, StoreOrderYandexAction $action): JsonResponse
    {
        return $action($request);
    }

    /**
     * @param Request $request
     * @param CancelInfoOrderYandexAction $action
     * @return JsonResponse
     */
    public function cancelInfoOrderYandex(Request $request, CancelInfoOrderYandexAction $action): JsonResponse
    {
        return $action($request);
    }

    /**
     * @param Request $request
     * @param CancelOrderYandexAction $action
     * @return JsonResponse
     */
    public function cancelOrderYandex(Request $request, CancelOrderYandexAction $action): JsonResponse
    {
        return $action($request);
    }

    /**
     * @param Request $request
     * @param AcceptOrderYandexAction $action
     * @return JsonResponse
     */
    public function acceptOrderYandex(Request $request, AcceptOrderYandexAction $action): JsonResponse
    {
        return $action($request);
    }

    /**
     * @param Order $order
     * @param GetOrdersInYandexAction $action
     * @return JsonResponse
     */
    public function getOrdersInYandex(Order $order, GetOrdersInYandexAction $action): JsonResponse
    {
        return $action($order);
    }

    /**
     * берёт заказы которые ожидают оценки и отменяет заказ, который дороже по стоимости
     * @param Request $request
     * @param GetOptimalOrderInYandexAction $action
     * @return JsonResponse
     */
    public function getOptimalOrderInYandex(Request $request, GetOptimalOrderInYandexAction $action): JsonResponse
    {
        return $action($request);
    }

    /**
     * @param Request $request
     * @param GetDriverPositionYandex $action
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function getDriverPositionYandex(Request $request, GetDriverPositionYandex $action): JsonResponse
    {
        return $action($request);
    }

    /**
     * @param Order $order
     * @param CancelUnpaidOrderAction $action
     * @return RedirectResponse
     */
    public function cancelUnpaidOrder(Order $order, CancelUnpaidOrderAction $action): RedirectResponse
    {
        return $action($order);
    }

    /**
     * @param Order $order
     * @param RestorePaidOrderAction $action
     * @return RedirectResponse
     */
    public function restorePaidOrder(Order $order, RestorePaidOrderAction $action): RedirectResponse
    {
        return $action($order);
    }

    /**
     * @param Order $order
     * @param Request $request
     * @param CancelMobileApplicationPaidOrderAction $action
     * @return JsonResponse
     */
    public function cancelMobileApplicationPaidOrder(Order $order, Request $request, CancelMobileApplicationPaidOrderAction $action): JsonResponse
    {
        return $action($order, $request);
    }

    /**
     * @param Order $order
     * @param Request $request
     * @param CancelOtherPaidOrderAction $action
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function cancelOtherPaidOrder(Order $order, Request $request, CancelOtherPaidOrderAction $action): JsonResponse
    {
        return $action($order, $request);
    }

    /**
     * @param LiveOrdersAction $action
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function liveOrders(LiveOrdersAction $action): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return $action();
    }
}
