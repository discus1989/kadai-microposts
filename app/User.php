<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userID)
    {
        //既にフォローしているかの確認
        $exist = $this->is_following($userID);
        //相手が自分ではないかの確認
        $its_me = $this->id == $userID;
        
        if($exist || $its_me){
            //既にフォローしていれば何もしない
            return false;
        } else {
            //未フォローであればフォローする
            $this->followings()->attach($userID);
            return true;
        }
    }
    
    public function unfollow($userID)
    {
        //既にフォローしているかの確認
        $exist = $this->is_following($userID);
        //相手が自分自身かどうかの確認
        $its_me = $this->id == $userID;
        
        if($exist && !$its_me){
            //既にフォローをしていればフォローを外す
            $this->followings()->detach($userID);
            return true;
        } else {
            //未フォローであれば何もしない
            return false;
        }
    }
    
    public function is_following($userID)
    {
        return $this->followings()->where('follow_id', $userID)->exists();
    }
    
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()->pluck('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        return Micropost::whereIn('user_id',$follow_user_ids);
    }
    
//お気に入り機能の実装
    public function favorites()
    {
        return $this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'micropost_id')->withTimestamps();
    }
//フォロー機能    
    public function favorite($micropostId)
    {

        $exist = $this->is_favorites($micropostId);
//        $its_me = $this->id == $micropostId;
        
        if ($exist) //修正
        {
            return false;
        } else {
            $this->favorites()->attach($micropostId);
            return true;
        }
    }
//アンフォロー機能    
    public function unfavorite($micropostId)
    {
        $exist = $this->is_favorites($micropostId);
//        $its_me = $this->id == $micropostId;
        
        if ($exist) //修正
        {
            $this->favorites()->detach($micropostId);
            return true;
        } else {
            return false;
        }
    }
    public function is_favorites($micropostId)
    {
        return $this->favorites()->where('micropost_id', $micropostId)->exists();
    }
}
