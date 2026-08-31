<?php

namespace App\Utility;

use App\Models\Category;

class CategoryUtility
{
    /**
     * In-process cache of the full parent_id → children map.
     * Built once per request from a single query; reused by all
     * descendant lookups to eliminate recursive N+1 queries.
     */
    private static ?array $parentChildMap = null;

    /**
     * Load the entire category parent→children map in one query.
     */
    private static function getParentChildMap(): array
    {
        if (self::$parentChildMap === null) {
            $all = Category::select('id', 'parent_id')
                ->orderBy('order_level', 'desc')
                ->get();

            self::$parentChildMap = [];
            foreach ($all as $cat) {
                self::$parentChildMap[$cat->parent_id][] = $cat->id;
            }
        }

        return self::$parentChildMap;
    }

    /**
     * Collect all descendant IDs using the in-memory map (zero extra queries).
     */
    private static function collectDescendantIds(int $parentId, array $map): array
    {
        $ids = [];
        if (!isset($map[$parentId])) {
            return $ids;
        }
        foreach ($map[$parentId] as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, self::collectDescendantIds($childId, $map));
        }
        return $ids;
    }

    /*when with trashed is true id will get even the deleted items*/
    public static function get_immediate_children($id, $with_trashed = false, $as_array = false)
    {
        $children = $with_trashed ? Category::where('parent_id', $id)->orderBy('order_level', 'desc')->get() : Category::where('parent_id', $id)->orderBy('order_level', 'desc')->get();
        $children = $as_array && !is_null($children) ? $children->toArray() : $children;

        return $children;
    }

    public static function get_immediate_children_ids($id, $with_trashed = false)
    {

        $children = CategoryUtility::get_immediate_children($id, $with_trashed, true);

        return !empty($children) ? array_column($children, 'id') : array();
    }

    public static function get_immediate_children_count($id, $with_trashed = false)
    {
        return $with_trashed ? Category::where('parent_id', $id)->count() : Category::where('parent_id', $id)->count();
    }

    /*when with trashed is true id will get even the deleted items*/
    public static function flat_children($id, $with_trashed = false, $container = array())
    {
        $children = CategoryUtility::get_immediate_children($id, $with_trashed, true);

        if (!empty($children)) {
            foreach ($children as $child) {

                $container[] = $child;
                $container = CategoryUtility::flat_children($child['id'], $with_trashed, $container);
            }
        }

        return $container;
    }

    /**
     * Get all descendant category IDs using a single-query in-memory tree.
     * Replaces the old recursive N+1 implementation.
     */
    public static function children_ids($id, $with_trashed = false)
    {
        // Fast path: use the in-memory map (1 query total per request).
        $map = self::getParentChildMap();
        return self::collectDescendantIds((int) $id, $map);
    }

    public static function category_tree_ids($category, $category_ids)
    {
        return array_merge($category_ids, self::children_ids($category->id));
    }

    public static function move_children_to_parent($id)
    {
        $children_ids = CategoryUtility::get_immediate_children_ids($id, true);

        $category = Category::where('id', $id)->first();

        CategoryUtility::move_level_up($id);

        Category::whereIn('id', $children_ids)->update(['parent_id' => $category->parent_id]);
    }

    public static function create_initial_category($key)
    {
        return true;
    }

    public static function move_level_up($id)
    {
        if (CategoryUtility::get_immediate_children_ids($id, true) > 0) {
            foreach (CategoryUtility::get_immediate_children_ids($id, true) as $value) {
                $category = Category::find($value);
                $category->level -= 1;
                $category->save();
                return CategoryUtility::move_level_up($value);
            }
        }
    }

    public static function move_level_down($id)
    {
        if (CategoryUtility::get_immediate_children_ids($id, true) > 0) {
            foreach (CategoryUtility::get_immediate_children_ids($id, true) as $value) {
                $category = Category::find($value);
                $category->level += 1;
                $category->save();
                return CategoryUtility::move_level_down($value);
            }
        }
    }

    public static function update_child_level($id)
    {
        $get_immediate_children_ids = CategoryUtility::get_immediate_children_ids($id, true);
        if (count($get_immediate_children_ids) > 0) {
            $parent_category = Category::find($id);
            foreach ($get_immediate_children_ids as $value) {
                $category = Category::find($value);
                $category->level = $parent_category->level + 1;
                $category->save();
                CategoryUtility::update_child_level($value);
            }
        }
    }

    public static function delete_category($id)
    {
        $category = Category::where('id', $id)->first();
        if (!is_null($category)) {
            CategoryUtility::move_children_to_parent($category->id);
            $category->delete();
        }
    }
}
