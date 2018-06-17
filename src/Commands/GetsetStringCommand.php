<?php
/*
 * This file is part of the Redmodel package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limen\RedModel\Commands;

class GetsetStringCommand extends Command
{
    public function getScript()
    {
        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? 1 : 0;

        $script = <<<LUA
    local values = {}; 
    local setTtl = $setTtl;
    for i,v in ipairs(KEYS) do 
        local ttl = redis.pcall('ttl', v);
        values[#values+1] = redis.pcall('getset',v,ARGV[1]); 
        if setTtl == 1 then
            $luaSetTtl
        elseif ttl >= 0 then
            redis.pcall('expire',v,ttl)
        end
    end 
    return {KEYS,values};
LUA;
        return $script;
    }
}