<?php


namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class MenuController extends BaseController
{
    /* TODO: complete getMenuItems so that it returns a nested menu structure from the database
    Requirements
    - the eloquent expressions should result in EXACTLY one SQL query no matter the nesting level or the amount of menu items.
    - post process your results in PHP
    - it should work for infinite level of depth (children of childrens children of childrens children, ...)
    - verify your solution with `php artisan test`
    - do a `git commit && git push` after you are done or when the time limit is over

    Hints:
    - imagine a maximum of a few hundred menu items
    - partial or not working answers also get graded so make sure you commit what you have

    Sample response on GET /menu:
    ```json
    [
        {
            "id": 1,
            "name": "All events",
            "url": "/events",
            "parent_id": null,
            "created_at": "2021-04-27T15:35:15.000000Z",
            "updated_at": "2021-04-27T15:35:15.000000Z",
            "children": [
                {
                    "id": 2,
                    "name": "Laracon",
                    "url": "/events/laracon",
                    "parent_id": 1,
                    "created_at": "2021-04-27T15:35:15.000000Z",
                    "updated_at": "2021-04-27T15:35:15.000000Z",
                    "children": [
                        {
                            "id": 3,
                            "name": "Illuminate your knowledge of the laravel code base",
                            "url": "/events/laracon/workshops/illuminate",
                            "parent_id": 2,
                            "created_at": "2021-04-27T15:35:15.000000Z",
                            "updated_at": "2021-04-27T15:35:15.000000Z",
                            "children": []
                        },
                        {
                            "id": 4,
                            "name": "The new Eloquent - load more with less",
                            "url": "/events/laracon/workshops/eloquent",
                            "parent_id": 2,
                            "created_at": "2021-04-27T15:35:15.000000Z",
                            "updated_at": "2021-04-27T15:35:15.000000Z",
                            "children": []
                        }
                    ]
                },
                {
                    "id": 5,
                    "name": "Reactcon",
                    "url": "/events/reactcon",
                    "parent_id": 1,
                    "created_at": "2021-04-27T15:35:15.000000Z",
                    "updated_at": "2021-04-27T15:35:15.000000Z",
                    "children": [
                        {
                            "id": 6,
                            "name": "#NoClass pure functional programming",
                            "url": "/events/reactcon/workshops/noclass",
                            "parent_id": 5,
                            "created_at": "2021-04-27T15:35:15.000000Z",
                            "updated_at": "2021-04-27T15:35:15.000000Z",
                            "children": []
                        },
                        {
                            "id": 7,
                            "name": "Navigating the function jungle",
                            "url": "/events/reactcon/workshops/jungle",
                            "parent_id": 5,
                            "created_at": "2021-04-27T15:35:15.000000Z",
                            "updated_at": "2021-04-27T15:35:15.000000Z",
                            "children": []
                        }
                    ]
                }
            ]
        }
    ]
     */

    public function getMenuItems()
    {
        try{
            $menuItems = MenuItem::with('childrenRecursive')
                ->whereNull('parent_id')
                ->orderBy('id')
                ->get()
                ->toArray();

            // Transform MenuItems to get testcased passed
            return $this->transformMenuItems($menuItems);

        } catch (\Exception $e) {
            Log::error('An error occurred while fetching events with workshops: ' . $e->getMessage());

            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    private function transformMenuItems($menuItems)
    {
        try{
            return array_map(function ($menuItem) {
                $children = $menuItem['children_recursive'];
                unset($menuItem['children_recursive']);

                // Check if the menu item has children
                if (!empty($children)) {
                    // Recursively transform the children menu items
                    $menuItem['children'] = $this->transformMenuItems($children);
                } else {
                    // If no children, set an empty array for consistency
                    $menuItem['children'] = [];
                }

                return $menuItem;
            }, $menuItems);

        } catch (\Exception $e) {
            Log::error('An error occurred while fetching events with workshops: ' . $e->getMessage());

            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }
}
