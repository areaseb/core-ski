<?php

namespace Areaseb\Core\Models;

class Category extends Primitive
{
    public function products()
    {
        return $this->morphedByMany(Product::class, 'categorizable');
    }

    public function expenses()
    {
        return $this->morphedByMany(Expense::class, 'categorizable');
    }

    public static function categoryOf($class)
    {
        $result = \DB::table('categorizables')->where('categorizable_type', 'like', '%'. $class . '%')->select('category_id')->get();
        $catIds = [];
        foreach ($result as $key => $value)
        {
            $catIds[] = $value->category_id;
        }
        return self::whereIn('id', $catIds);
    }

}
