
namespace <?=$namespace?>

use Concise\Http\Request;

class <?=$className?><?="\r\n"?>
{
	public function handle (Request $request,\Closure $next = null)
	{
		return $next($request);
	}
}