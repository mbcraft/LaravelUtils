<?php

namespace Mbcraft\Laravel\Database;

trait SoftDeletesCascade {
    
    public function delete() {
        
        if (method_exists($this, "runSoftDelete")) {
            $cascades = $this->cascades;
            
            foreach ($cascades as $access_method) {
                $cascade_objects = $this->{$access_method}()->get()->toBase()->all();
                foreach ($cascade_objects as $objs) {
                    $objs->delete();
                }
            }
        }
        
        parent::delete();
    }
    
}