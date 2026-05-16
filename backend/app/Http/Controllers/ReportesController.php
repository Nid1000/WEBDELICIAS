<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    public function ventasDiarias(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $q = DB::table('pedidos')
            ->select([DB::raw("DATE(created_at) as fecha"), DB::raw("SUM(total) as total")])
            ->where('estado', '<>', 'cancelado')
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy(DB::raw("DATE(created_at)"), 'asc');

        if ($desde) {
            $q->where('created_at', '>=', $desde);
        }
        if ($hasta) {
            $q->where('created_at', '<=', $hasta);
        }

        $rows = $q->get()->map(fn ($r) => ['fecha' => $r->fecha, 'total' => (float) $r->total]);
        return response()->json(['data' => $rows], 200);
    }

    public function ventasSemanales(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        // Semana = lunes. MySQL: YEARWEEK(date, 3) => Monday-based.
        $q = DB::table('pedidos')
            ->select([
                DB::raw("DATE_SUB(DATE(created_at), INTERVAL (WEEKDAY(created_at)) DAY) as semana"),
                DB::raw("SUM(total) as total"),
            ])
            ->where('estado', '<>', 'cancelado')
            ->groupBy(DB::raw("DATE_SUB(DATE(created_at), INTERVAL (WEEKDAY(created_at)) DAY)"))
            ->orderBy(DB::raw("DATE_SUB(DATE(created_at), INTERVAL (WEEKDAY(created_at)) DAY)"), 'asc');

        if ($desde) {
            $q->where('created_at', '>=', $desde);
        }
        if ($hasta) {
            $q->where('created_at', '<=', $hasta);
        }

        $rows = $q->get()->map(fn ($r) => ['semana' => $r->semana, 'total' => (float) $r->total]);
        return response()->json(['data' => $rows], 200);
    }

    public function ventasMensuales(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $q = DB::table('pedidos')
            ->select([DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mes"), DB::raw("SUM(total) as total")])
            ->where('estado', '<>', 'cancelado')
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"), 'asc');

        if ($desde) {
            $q->where('created_at', '>=', $desde);
        }
        if ($hasta) {
            $q->where('created_at', '<=', $hasta);
        }

        $rows = $q->get()->map(fn ($r) => ['mes' => $r->mes, 'total' => (float) $r->total]);
        return response()->json(['data' => $rows], 200);
    }

    public function topProductos(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $limite = (int) ($request->query('limite', '10'));
        $limite = $limite > 0 ? $limite : 10;

        $q = DB::table('pedido_detalles as d')
            ->join('pedidos as p', 'p.id', '=', 'd.pedido_id')
            ->leftJoin('productos as pr', 'pr.id', '=', 'd.producto_id')
            ->select([
                'd.producto_id as producto_id',
                DB::raw('MAX(pr.nombre) as nombre'),
                DB::raw('MAX(pr.imagen) as imagen'),
                DB::raw('SUM(d.cantidad) as cantidad'),
                DB::raw('SUM(d.subtotal) as subtotal'),
            ])
            ->whereNotNull('d.producto_id')
            ->where('p.estado', '<>', 'cancelado')
            ->groupBy('d.producto_id')
            ->orderBy(DB::raw('SUM(d.cantidad)'), 'desc');

        if ($desde) {
            $q->where('p.created_at', '>=', $desde);
        }
        if ($hasta) {
            $q->where('p.created_at', '<=', $hasta);
        }

        $rows = $q->limit($limite)->get()->map(function ($r) {
            return [
                'producto_id' => (int) $r->producto_id,
                'nombre' => (string) ($r->nombre ?: ('Producto '.(int) $r->producto_id)),
                'imagen' => $r->imagen,
                'cantidad' => (int) $r->cantidad,
                'subtotal' => (float) $r->subtotal,
            ];
        });

        return response()->json(['data' => $rows], 200);
    }

    public function topCategorias(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $limite = (int) ($request->query('limite', '10'));
        $limite = $limite > 0 ? $limite : 10;

        $q = DB::table('pedido_detalles as d')
            ->join('pedidos as p', 'p.id', '=', 'd.pedido_id')
            ->leftJoin('productos as pr', 'pr.id', '=', 'd.producto_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'pr.categoria_id')
            ->select([
                DB::raw('pr.categoria_id as categoria_id'),
                DB::raw('MAX(c.nombre) as nombre'),
                DB::raw('SUM(d.cantidad) as cantidad'),
                DB::raw('SUM(d.subtotal) as subtotal'),
            ])
            ->whereNotNull('d.producto_id')
            ->whereNotNull('pr.categoria_id')
            ->where('p.estado', '<>', 'cancelado')
            ->groupBy('pr.categoria_id')
            ->orderBy(DB::raw('SUM(d.cantidad)'), 'desc');

        if ($desde) {
            $q->where('p.created_at', '>=', $desde);
        }
        if ($hasta) {
            $q->where('p.created_at', '<=', $hasta);
        }

        $rows = $q->limit($limite)->get()->map(function ($r) {
            return [
                'categoria_id' => (int) $r->categoria_id,
                'nombre' => (string) ($r->nombre ?: ('Categoría '.(int) $r->categoria_id)),
                'cantidad' => (int) $r->cantidad,
                'subtotal' => (float) $r->subtotal,
            ];
        });

        return response()->json(['data' => $rows], 200);
    }
}
