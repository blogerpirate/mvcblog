<?php
	
	namespace application\models;

	use application\core\Model;
	// use Imagick;

	class Admin extends Model
	{
		public $error;

		public function loginValidate($post) {
			$config = require 'application/config/admin.php';
			if($config['login'] != $post['login'] || $config['password'] != $post['password']) {
				$this->error = 'Логин или пароль указан неверно';
				return false;
			}
			return true;
		}

		public function postValidate($post, $type) {
			$namelen = iconv_strlen($post['name']);
			$descriptionlen = iconv_strlen($post['description']);
			$textlen = iconv_strlen($post['text']);
			if($namelen < 3 || $namelen > 100) {
				$this->error = 'Название должно содержать от 3 до 100 символов';
				return false;
			} elseif($descriptionlen < 3 || $descriptionlen > 100) {
				$this->error = 'Описсание должно содержать от 3 до 100 символов';
				return false;
			} if($textlen < 3 || $textlen > 5000) {
				$this->error = 'Текст должно содержать от 10 до 5000 символов';
				return false;
			}

			if(empty($_FILES['img']['tmp_name']) && $type == 'add'){
				$this->error = 'Изображение не выбрано';
				return false;
			}

			return true;
		}


		public function postAdd($post) {
			$params = [
				'id' => null,
				'name' => $post['name'],
				'description' => $post['description'],
				'text' => $post['text'],
			];
			$this->db->query('INSERT INTO posts VALUES (:id, :name, :description, :text)', $params);
			return $this->db->lastInsertId();
		}

		public function postEdit($post, $id) {
			$params = [
				'id' => $id,
				'name' => $post['name'],
				'description' => $post['description'],
				'text' => $post['text'],
			];
			$this->db->query('UPDATE posts SET name = :name, description = :description, text = :text WHERE id = :id', $params);
		}

		public function postUploadImage($path, $id) {
			// $img = new Imagick($path);	На OpenServer класс Imagick не работает, на реальном хостинге должен
			// $img->cropThumbnailImage(1080, 540);
			// $img->setImageCompressionQuality(80);
			// $img->writeImage('public/materials/'.$id.'.jpg');
			move_uploaded_file($path, 'public/materials/'.$id.'.jpg');
		}

		public function isPostExists($id) {
			$params = [
				'id' => $id,
			];
			return $this->db->column('SELECT id FROM posts WHERE id = :id', $params);
		}

		public function postDelete($id) {
			$params = [
				'id' => $id,
			];
			$this->db->query('DELETE FROM posts WHERE id = :id', $params);
			unlink('public/materials/'.$id.'.jpg');
		}

		public function postData($id) {
			$params = [
				'id' => $id,
			];
			return $this->db->row('SELECT * FROM posts WHERE id = :id', $params);
		}
	}