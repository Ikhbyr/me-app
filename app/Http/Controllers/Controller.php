<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //
    public function applyFilters($query, $filters)
    {
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                switch (strtolower($filter['cond'])) {
                    case "like":
                        $query->whereRaw("upper(" . $filter['field'] . ") like upper('" . $filter['value'] . "')");
                        break;
                    case "notnull":
                    case "NOTNULL":
                        $query->whereNotNull($filter['field']);
                        break;
                    case "null":
                    case "NULL":
                        $query->whereNull($filter['field']);
                        break;
                    default:
                        if (is_array($filter['value'])) {
                            $query->whereIn($filter['field'], $filter['value']);
                        } else {
                            $query->where($filter['field'], $filter['cond'], $filter['value']);
                        }
                        break;
                }
                // if ($filter['cond'] == "like"){
                //     $query->whereRaw("upper(" . $filter['field'] . ") " . $filter['cond'] . " upper('" . $filter['value'] . "')");
                // } e
            }
        }
        return $query;
    }

    public function applyOrders($query, $orders)
    {
        if (is_array($orders)) {
            foreach ($orders as $order) {
                $query->orderBy($order['field'], $order['dir']);
            }
        }
        return $query;
    }

    public function applyPaginate($query, $perPage, $page)
    {
        $query = $query->simplePaginate(isset($perPage) ? $perPage : 50, ['*'], 'page', isset($page) ? $page : 1);
        return $query;
    }

    /**
     * allServiceList
     *
     * @param  mixed $query
     * @param  mixed $data [filters, orders, perPage, page]
     * @return void
     */
    public function allServiceList($query, $data) {
        $query = $this->applyFilters($query, @$data['filters']);
        $query = $this->applyOrders($query, @$data['orders']);
        return $this->applyPaginate($query, @$data['perPage'], @$data['page']);
    }
}
