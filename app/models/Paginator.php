<?php 
namespace app\models;

class Paginator
{
	public $currentPage;
	public $perPage;
	public $total;
	public $countPages;
	public $uri;

	public function __construct($page, $perPage, $total)
	{
		$this->perPage 		= $perPage;
		$this->total 		= $total;
		$this->countPages 	= $this->getCountPage();
		$this->uri 			= $this->getParams();
		$this->currentPage 	= $this->getCurrentPage($page);
	}

	// 15:00 - https://www.youtube.com/watch?v=JWX6SsIrFpY&list=LLgpLpfOX1QHKa4srecxgSKQ&index=2&t=0s
	public function __toString()
	{
		return $this->getHtml();
	}

	public function getCurrentPage($page)
	{
		if( !$page || $page < 1 ) $page = 1;
		if( $page > $this->countPages ) $page = $this->countPages;
		return $page;
	}

	public function getCountPage()
	{
		return ceil($this->total / $this->perPage) ?: 1;
	}

	public function getStart()
	{
		return ( $this->currentPage - 1 ) * $this->perPage;
	}

	public function getParams()
	{
		$url = $_SERVER['REQUEST_URI'];
		$url = explode('?', $url);
		$uri = $url[0] . '?';

		if ( isset($url[1]) && $url['1'] != '' ) {
			$params = explode('&', $url[1]);

			foreach ($params as $param) {
				if ( !preg_match('#page=#', $param) ) {
					$uri .= "{$param}&";
				}
			}
		}
		return $uri;
	}

	public function getHtml()
	{
		$back 		= null; // ссылка НАЗАД
		$forward 	= null; // ссылка ВПЕРЕД
		$startPage 	= null; // ссылка А НАЧАЛО
		$endPage 	= null; // ссылка В КОНЕЦ
		$page2left 	= null; // вторая страница слева
		$page1left 	= null; // первая страница слева
		$page2right = null; // вторая старница справа
		$page1right = null; // первая страница справа

		if ( $this->currentPage > 1 ) {
			$back = "<li class='page-item ml-1' title='Назад'><a class='page-link rounded' href='{$this->uri}page=" . ($this->currentPage - 1) . "'><i style='padding: 3px 1px 4px 1px;' class='fa fa fa-angle-left'></i></a></li>";
		}

		if ( $this->currentPage < $this->countPages ) {
			$forward = "<li class='page-item ml-1' title='Вперед'><a class='page-link rounded' href='{$this->uri}page=" . ($this->currentPage + 1) . "'><i style='padding: 3px 1px 4px 1px;' class='fa fa fa-angle-right'></i></a></li>";
		}

		if ( $this->currentPage > 3 ) {
			$startPage = "<li class='page-item ml-1' title='В начало'><a class='page-link rounded' href='{$this->uri}page=1'><i style='padding: 3px 1px 4px 1px;' class='fa fa-angle-double-left'></i></a></li>";
		}

		if ( $this->currentPage < ($this->countPages -2) ) {
			$endPage = "<li class='page-item ml-1' title='В конец'><a class='page-link rounded' href='{$this->uri}page={$this->countPages}'><i style='padding: 3px 1px 4px 1px;' class='fa fa fa-angle-double-right'></i></a></li>";
		}

		if ( $this->currentPage - 2 > 0 ) {
			$page2left = "<li class='page-item ml-1'><a class='page-link rounded' href='{$this->uri}page=".($this->currentPage - 2)."'>".($this->currentPage - 2)."</a></li>";
		}

		if ( $this->currentPage - 1 > 0 ) {
			$page1left = "<li class='page-item ml-1'><a class='page-link rounded' href='{$this->uri}page=".($this->currentPage - 1)."'>".($this->currentPage - 1)."</a></li>";
		}

		if ( $this->currentPage + 2 <= $this->countPages ) {
			$page2right = "<li class='page-item ml-1'><a class='page-link rounded' href='{$this->uri}page=".($this->currentPage + 2)."'>".($this->currentPage + 2)."</a></li>";
		}

		if ( $this->currentPage + 1 <= $this->countPages ) {
			$page1right = "<li class='page-item ml-1'><a class='page-link rounded' href='{$this->uri}page=".($this->currentPage + 1)."'>".($this->currentPage + 1)."</a></li>";
		}

		return "<nav>
					<ul class='pagination pagination-sm m-0'>$startPage $back $page2left $page1left
						<li class='page-item ml-1 active'>
							<a class='page-link rounded'>$this->currentPage</a>
						</li>$page1right $page2right $forward $endPage 
					<ul>
				</nav>";
	}
}