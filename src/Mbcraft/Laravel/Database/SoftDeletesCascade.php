<?php

namespace Mbcraft\Laravel\Database;

trait SoftDeletesCascade {
    
    public function delete() {
        
        if (method_exists($this, "runSoftDelete")) {
            $softCascades = $this->softCascades;
            if ($softCascades!=null) {
                foreach ($softCascades as $access_method) {
                    $cascade_objects = $this->{$access_method}()->get()->toBase()->all();
                    foreach ($cascade_objects as $objs) {
                        $objs->delete();
                    }
                }
            } else
                \Log::debug("softCascades are null for Player!!");
        }
        
        parent::delete();
    }
    
}