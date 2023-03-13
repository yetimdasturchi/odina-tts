<?php

class Odina{
  
  function speak( $text ){
    if ( $parts = $this->tokenize( $text ) ) {
      $tmp = sprintf( 'tmp/%04x%04x.mp3', mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
      copy( 'templates/empty.mp3',  $tmp);
      
      $audio = file_get_contents( $tmp );

      $mp3 = NULL;

      foreach ($parts as $word) {
        foreach ($word as $token) {
          if ( file_exists( 'templates/'.$token.'.mp3' ) ) {
            $mp3 .= file_get_contents('templates/'.$token.'.mp3');
          }else{
            unlink( $tmp );
            throw new Exception("Audioni generatsiya qilish uchun \"{$token}\" tokeni topilmadi!");
          }
        }
      }
      
      file_put_contents($tmp, $mp3, FILE_APPEND);

      return $tmp;
    }
  }

  function tokenize( $text = '' ){
    preg_match_all('/[\p{Latin}]+/u', $text, $matches);
    
    if ( !empty( $matches ) ) {
      $tokens = $this->tokenize_request( implode(' ', $matches[0]) );
    
      $tokens = array_map(function( $x ){
        return explode('-', $x);
      }, $tokens);

      return $tokens;
    }

    return FALSE;
  }

  function tokenize_request( $text = '' ){
    
    $payload = json_encode( [
      "content"=> $text
    ], JSON_PRETTY_PRINT);
    
    $ch = curl_init( 'https://api.korrektor.uz/tools/tokenize' );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Authorization: Bearer D2~0$oau@Zp{Wy06B!Ye$DmUT(P1Q{$t'
    ]);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    
    $result = curl_exec($ch);
    curl_close($ch);

    $json = json_decode( $result, TRUE );
    
    return explode(' ', $json['content']);
  }
}
