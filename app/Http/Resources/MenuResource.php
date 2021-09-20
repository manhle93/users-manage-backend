<?php

namespace App\Http\Resources;

use App\Models\RoleMenu;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = Auth::user();
        $menuIds = RoleMenu::where('role_id', $user->role_id)->pluck('menu_id')->toArray();
        $children = collect($this->children)->whereIn('id', $menuIds)->toArray();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'icon' => $this->icon,
            'hidden' => $this->hidden,
            'order' => $this->order,
            'children' => $children,
        ];
    }
}
