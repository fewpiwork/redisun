<?php
/*
 * This file is part of the Redisun package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limen\Redisun\Commands;

class HmsetCommand extends Command
{
    public function getScript()
    {
        $argString = $this->joinArguments();
        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? 1 : 0;
        $checkExist = $this->existenceScript;
        $delScript = $this->deleteScript;

        $script = <<<LUA
$checkExist
local values = {}; 
local setTtl = '$setTtl';
for i,v in ipairs(KEYS) do 
    local ttl = redis.pcall('ttl', v)
    $delScript
    values[#values+1] = redis.pcall('hmset',v, $argString); 
    if setTtl == '1' then
        $luaSetTtl
    elseif ttl > 0 then
        redis.pcall('expire', v, ttl);
    end
end
return {KEYS,values};
LUA;
        return $script;
    }
}