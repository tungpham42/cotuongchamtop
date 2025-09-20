<?php
class Model
{
	public static function insert($table, $values)
	{
	    DB::ins()->insert($table, $values);
	}

	public static function select($table, $domain)
	{
        $q = "SELECT * FROM ".DB::ins()->table($table)." WHERE `Domain`= :domain";
        return DB::ins()->queryRow($q, array(
            ':domain'=>$domain
        ));
	}

	public static function delete($table, $domain)
	{
	    DB::ins()->delete($table, "Domain=:domain", array(
            ":domain"=>$domain,
        ));
	}

	public static function update($table, array $data, $domain)
	{
        DB::ins()->update($table, $data, "Domain=:domain", array(
            ":domain"=>$domain,
        ));
	}
}