<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RestController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected static $model;
    protected static $validations = [];
    protected static $indexColumns = [];
    protected static $treeColumns;
    protected static $with = [];
    protected static $orderBy;
    protected static $optionColumn;
    protected $primaryKey;
    protected $table;

    function __construct()
    {
        $this->primaryKey = (new static::$model)->getKeyName();
        $this->table = (new static::$model)->table;
    }

    protected function filter($query, $filter)
    {
        foreach ($filter as $key => $clause) {
            if ($key === 'pagination') {
            } elseif ($clause === '!null') {
                $query->whereNotNull($key);
            } elseif ($clause === 'null') {
                $query->whereNull($key);
            } elseif (substr($clause, 0, 3) === 'in(') {
                $query->whereIn($key, explode(',', substr($clause, 3, -1)));
            } elseif (substr($clause, 0, 2) === '!%') {
                $query->where($key, 'not like', substr($clause, 1));
            } elseif (substr($clause, 0, 1) === '!' && is_numeric($clause)) {
                $query->where($key, '!=', substr($clause, 1));
            } elseif (substr($clause, 0, 1) === '!') {
                $query->where($key, 'not like', substr($clause, 1) . '%');
            } elseif (is_numeric($clause)) {
                $query->where($key, '=', $clause);
            } else {
                $query->where($key, 'like', "$clause%");
            }
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::debug(">>index");
        $response = [];

        $query = static::$model::with(static::$with)
            ->select(static::$indexColumns);

        /* Filter */
        $this->filter($query, request()->all());

        /* Paginate */
        $pagination = request()->input('pagination', null);
        if ($pagination && $pagination['rowsPerPage']) {
            $query->offset(((int)$pagination['page'] - 1) * (int)$pagination['rowsPerPage']);
            $query->limit($pagination['rowsPerPage']);
        }

        /* Order */
        if ($pagination && $pagination['sortBy']) {
            if ($pagination['descending'] === 'true') {
                $query->orderByDesc($pagination['sortBy']);
            } else {
                $query->orderBy($pagination['sortBy']);
            }
        } else {
            foreach (static::$orderBy as $orderBy) {
                $query = $query->orderBy($orderBy);
            }
        }


        $response['data'] = $query->get()->toArray();
        if ($pagination) {
            $response['rowsNumber'] = $query->paginate($pagination['rowsPerPage'])->total();
        }
        return $response;
    }

    public function tree($nodeId = 'null')
    {
        $response = [];

        $query = static::$model::select(array_merge(["$this->primaryKey"], static::$treeColumns));

        /* Filter */
        $this->filter($query, request()->all());
        $this->filter($query, ['parent_node_id' => $nodeId]);

        /* Order */
        foreach (static::$orderBy as $orderBy) {
            $query = $query->orderBy($orderBy);
        }

        $response['data'] = $query->get()->toArray();

        foreach ($response['data'] as &$node) {
            $node['lazy'] = true;
        }
        return $response;
    }

    /**
     * Send listing of resources as options for a select controller.
     *
     * @return \Illuminate\Http\Response
     */
    public function options()
    {
        $query = static::$model::select([
            "$this->primaryKey AS value",
            static::$optionColumn . " AS label"
        ]);
        $this->filter($query, request()->all());
        foreach (static::$orderBy as $orderBy) {
            $query->orderBy($orderBy);
        }
        return $query->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\company  $company
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = static::$model::with(static::$with)->find($id);
        if (!$data) {
            throw new NotFoundHttpException();
        }
        return $data;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $row = static::$model::create(Request::validate(static::$validations));

        /* Index row */
        $query = static::$model::with(static::$with)
            ->select(static::$indexColumns);
        $this->filter($query, [$this->primaryKey => $row[$this->primaryKey]]);
        return $query->first();
    }

    public function update($id)
    {
        $data = Request::validate(static::$validations);
        $row = static::$model::find($id);
        if (!$row) {
            throw new NotFoundHttpException();
        }
        $row->fill($data)->save();

        /* Index row */
        $query = static::$model::with(static::$with)
            ->select(static::$indexColumns);
        $this->filter($query, [$this->primaryKey => $id]);
        return $query->first();
    }

    public function destroy($id)
    {
        static::$model::destroy($id);
    }
}