
namespace <?=$namespace?>

use Concise\Http\Request\FormRequest;

class <?=$className?>Request extends FormRequest
{	

	/**
	 * 验证规则
	 * @return array
	 */
	public function rule ()
	{
		return [];
	}
	
	/**
	 * 错误消息
	 * @return array
	 */
	public function message ()
	{
		return [];
	}
}