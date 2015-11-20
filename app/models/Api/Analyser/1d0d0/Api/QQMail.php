<?php
class Api_Analyser_1d0d0_Api_QQMailModel extends Api_Analyser_1d0d0_AbstractModel {
	const TYPE_INIT = 0x0001;
	const TYPE_LIST_INIT = 0x0002;
	const TYPE_LIST = 0x0003;
	function initUrl() {
		$this->checkLogin ();
		return $this->getTask ( array (
				'url' => 'https://mail.qq.com/cgi-bin/loginpage',
				'args' => array (
						'type' => self::TYPE_INIT
				)
		) );
	}
	private function getTask(array $task) {
		$default = array (
				'userAgent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4',
				'args' => array ()
		);
		$task = array_merge ( $task, $default );
		return $task;
	}
	function logout() {
		$this->getSession ()->del ();
	}
	function login($params) {
		$this->checkParamEmpty ( 'username', $params );
		$username = $params ['username'];
		$this->getSession ()->set ( 'username', $username );
		$this->getSession ()->set ( 'isLogin', true );
		return session_id ();
	}
	function report() {
		$this->checkLogin ();
		return 'test';
	}
	function analyse($param) {
		$this->checkLogin ();
		$res = '';
		if (! empty ( $param ['res'] )) {
			$res = json_decode ( $param ['res'], true );
		}
		if (empty ( $res )) {
			$this->methodException ( 'res is empty', 0 );
		}
		$task = array ();
		foreach ( $res as $v ) {
			if (empty ( $v ['url'] )) {
				$this->methodException ( 'url is empty', 1 );
			}
			$content = '';
			if (! empty ( $v ['content'] )) {
				$content = $v ['content'];
			}
			$args = '';
			if (! empty ( $v ['args'] )) {
				$args = $v ['args'];
			}
			$url = trim ( $v ['url'] );
			$table = Table_ContentModel::getInstance ();
			$row = $table->fetchRow ( array (
					'type=?' => 'qqmail',
					'md5=?' => md5 ( $url )
			) );
			if (! isset ( $row )) {
				$row = $table->createRow ();
			}
			$row->setFromArray ( array (
					'username' => $this->getSession ()->get ( 'username' ),
					'type' => 'qqmail',
					'md5' => md5 ( $url ),
					'url' => $url,
					'content' => $content,
					'args' => serialize ( $args ),
					'createTime' => date ( 'Y-m-d H:i:s' )
			) );
			$row->save ();
			if (empty ( $args ['type'] )) {
				$this->methodException ( 'type is empty', 2 );
			}
			$type = $args ['type'];
			switch ($type) {
				case self::TYPE_INIT :
					$task [] = $this->parseInit ( array (
							'url' => $url
					) );
					break;
				case self::TYPE_LIST_INIT :
					break;
				default :
					$this->methodException ( 'type is invalid, type=' . $type, 2 );
					break;
			}
		}
		return $task;
	}
	private function parseInit(array $r) {
		$url = $r ['url'];
		$query = parse_url ( $url );
		if (! empty ( $query ['query'] )) {
			$query = $query ['query'];
			parse_str ( $query, $sid );
			if (! empty ( $sid ['sid'] )) {
				$sid = $sid ['sid'];
				$url = 'http://w.mail.qq.com/cgi-bin/mail_list?ef=js&r=0.014230005443096161&t=mobile_data.json&s=list&cursor=max&cursorutc=1443669780&cursorid=ZC2701-xVJGv6QDOQwAPyB2t61~N5a&cursorcount=20&folderid=1&device=ios&app=phone&ver=app&sid=';
				$task = array ();
				$task ['url'] = $url . $sid;
				$task ['args'] = array (
						'type' => self::TYPE_LIST_INIT
				);
				return $this->getTask ( $task );
			}
		}
	}
	private function parseListInit(array $r) {
	}
}