<?
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author		Atul Atri
 *
 * @package	Basecamp
 * @copyright	Copyright (c) 2001-2012, Kayako
 * @license		http://www.kayako.com/license
 * @link		http://www.kayako.com
 *
 * ###############################################
 */

/**
 * Todo model
 *
 * @author Varun Shoor
 */
class SWIFT_TodoTicketLink extends SWIFT_Model
{
	const TABLE_NAME		=	'basecamptodoticketlinks';
	const PRIMARY_KEY		=	'basecamptodoticketlinkid';

	const TABLE_STRUCTURE	=	"basecamptodoticketlinkid I PRIMARY AUTO NOTNULL,
								ticketid I DEFAULT '0' NOTNULL,
								todoid I DEFAULT '0' NOTNULL,
								projectid I DEFAULT '0' NOTNULL";

	const INDEX_1			=	'ticketid';
	const INDEX_2			=	'todoid';

	/**
	 * Check if ticket id is already linked to basecamp task
	 *
	 * @param int $_ticketId ticket id
	 *
	 * @return int todo item id on basecamp or false
	 */
	public static function getTodoInfo($_ticketId){
		$_query = "select * from ". TABLE_PREFIX.self::TABLE_NAME. " where  ticketid = $_ticketId";

		$_Swift = SWIFT::GetInstance();
		$_result = $_Swift->Database->QueryFetch($_query);

		if($_result === false){
			return false;
		}

		return $_result;
	}
}