<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* WordPress model for Codeigniter
*
* This class adds interaction to the website and a WordPress database.
* Contains common operations such as fetch posts, comments, and the modification of such.
*
* This library shouldn't have any dependencies other than a correctly formed WordPress database - not even the actual WordPress installation.
*
* @author	Mario Cuba <mario@mariocuba.net>
* @see		http://mariocuba.net, http://github.com/AeroCross
*/
class Wp extends CI_Model {	

	// the name of the WordPress database to use
	private $cdb = WP_DATABASE;
	private $postFields;
	
	public function __construct() {
		parent::__construct();
		
		$this->cdb 			= $this->load->database($this->cdb, TRUE);		
		$this->postFields 	= array(
				'id', 
				'guid',
				'post_title',
				'post_content',
				'post_excerpt',
				'post_date'
			);

	}

	/**
	* Executes the database query and return only one result.
	*
	* @param	string	- the table name excecute the query in
	* @return	object	- a Codeigniter database object with the result set, FALSE otherwise
	* @access	public
	*/
	public function get($table = 'posts') {
		return $this->cdb->get($table)->row();
	}

	/**
	* Executes the database query and return all the results.
	*
	* @param	string	- the table name excecute the query in
	* @return	object	- a Codeigniter database object with the result set, FALSE otherwise
	*/
	public function getAll($table = 'posts') {
		return $this->cdb->get($table)->result();
	}

	/**
	* Selects post information.
	*
	* @param	array	- the fields to fetch
	* @return	object	- the database object
	* @access	public
	*/
	public function post($fields = NULL, $post_id = NULL) {
		// check for class-member default value
		if ($fields == NULL) {
			$fields = $this->postFields;
		}

		$this->cdb
		->select($fields)
		->where('post_type', 'post')
		->where('post_status', 'publish');

		if (!empty($post_id)) {
			$this->cdb->where('id', $post_id);
		}
		
		return $this;
	}

	/**
	* Alias of post().
	*
	* @param	array	- the fields to fetch
	* @return	object	- the database object
	* @access	public
	*/
	public function posts($fields = NULL) {
		// check for class-member default value
		if ($fields == NULL) {
			$fields = $this->postFields;
		}

		return $this->post($fields);
	}

	/**
	* Limits and offsets the results.
	*
	* @param	int		- the limit
	* @param	int		- the offset
	* @return	object	- the database object
	* @access	public
	*/
	public function only($amount, $offset = NULL) {
		if (!empty($offset)) {
			$this->cdb->limit($amount, $offset);
		} else {
			$this->cdb->limit($amount);
		}
		
		return $this;
	}

	/**
	* Selects the latest information according to the parameter.
	*
	* @param	int		- the amount of posts to fetch
	* @param	string	- the field to order by
	* @return	object	- the database object
	* @access	public
	*/
	public function latest($amount, $order = 'post_date') {
		$this->cdb
		->limit($amount)
		->order_by($order, 'desc');

		return $this;
	}

	/**
	* Selects meta information from a post.
	*
	* @param	string	- the meta key
	* @param	int		- the post id
	* @return	object	- the database object
	* @access	public
	*/
	public function meta($key, $post_id) {
		$this->cdb
		->select('meta_value')
		->where('meta_key', $key)
		->where('post_id', $post_id);
		
		return $this;
	}

	/**
	* Gets the taxonomy of a post.
	*
	* This is useful for fetching tags, categories, and such.
	*
	* @internal	for use with the categories() and tags() methods
	* @param	int		- the post id to fetch taxonomies from
	* @return	object	- the database object
	* @access	public
	*/
	public function taxonomy($post_id) {
		$this->cdb
		->select('terms.name')
		->join('term_taxonomy', 'terms.term_id = term_taxonomy.term_id')
		->join('term_relationships', 'terms.term_id = term_relationships.term_taxonomy_id')
		->join('posts', 'term_relationships.object_id = posts.id')
		->where('posts.id', $post_id);

		return $this;
	}

	/**
	* Selects categories.
	*
	* @internal	for use with the taxonomy() method
	* @return	object	- the database object
	* @access	public
	*/
	public function category() {
		$this->cdb->where('term_taxonomy.taxonomy', 'category');

		return $this;
	}

	/**
	* Alias of category().
	*
	* @access	public
	*/
	public function categories() {
		return $this->category();
	}

	/**
	* Selects tags.
	*
	* @internal	for use with the taxonomy() method
	* @return	object	- the database object
	* @access	public
	*/
	public function tag() {
		$this->cdb->where('term_taxonomy.taxonomy', 'post_tag');

		return $this;
	}

	/**
	* Alias of tag().
	*
	* @access	public
	*/
	public function tags() {
		return $this->tag();
	}

	/**
 	* Calculates the amount of comments for a single post.
 	*
 	* @param	int	- the post used to calculate comments
 	* @return	int	- the number of comments
	* @access	public
	*
	* @TODO:	check if the post doesn't exists so it returns a correct value
 	*/
	public function getTotalComments($post_id) {
		$this->cdb
		->select('comment_ID')
		->from('comments')
		->where('comment_post_ID', $post_id);
		
		return $this->cdb->get()->num_rows();
	}

	/**
 	* Gets the list of categories.
 	*
 	* @param	string	- the database column to order by. It takes a "table.column" string (usually from the "terms" table)
 	* @return	object	- a database object with the list of categories, FALSE otherwise
 	*
 	* @TODO: Code an easier way to order the result set.
 	*/
	public function getCategories($order = 'terms.term_id') {
		$this->cdb
		->select('name', 'slug')
		->from('terms')
		->join('term_taxonomy', 'terms.term_id = term_taxonomy.term_id')
		->where('taxonomy', 'category')
		->order_by($order);
		
		$sql = $this->cdb->get();
		
		if ($sql->num_rows() > 0) {
			return $sql;
		} else {
			return FALSE;
		}
	}
}