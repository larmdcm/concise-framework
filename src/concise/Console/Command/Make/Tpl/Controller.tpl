
namespace <?=$namespace?>

use Concise\Http\Request;

class <?=$className?>Controller
{	
	/**
	 * 显示资源列表
	 * @method GET
	 * @return Concise\Http\Response
	 */
	public function index ()
	{
	}
	
	/**
	 * 显示单个资源列表
	 * @method GET
 	 * @param integer $id
	 * @return Concise\Http\Response
	 */
	public function show ($id)
	{
	}

	/**
	 * 显示创建资源表单
	 * @method GET
	 * @return Concise\Http\Response
	 */
	public function create ()
	{
	}

	/**
	 * 存储资源请求
	 * @method POST
	 * @param Request $request
	 * @return Concise\Http\Response
	 */
	public function store (Request $request)
	{
	}

	/**
	 * 显示编辑资源表单
	 * @method GET
	 * @param integer $id
	 * @return Concise\Http\Response
	 */
	public function edit ($id)
	{
	}

	/**
	 * 编辑资源请求
 	 * @method PUT
	 * @param  integer  $id      
	 * @param  Request $request 
	 * @return Concise\Http\Response      
	 */
	public function update ($id,Request $request)
	{
	}

	/**
	 * 删除资源
	 * @method DELETE
	 * @param integer $id
	 * @return Concise\Http\Response
	 */
	public function destroy ($id)
	{
	}
}