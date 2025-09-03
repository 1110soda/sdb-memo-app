<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('memos', function (Blueprint $table) {
//      user_id: ログイン機能用、title: ない場合はメモの一行目/一文目/内容*（時間があればLLMを通してメモのタイトルを生成してもらう）をタイトルにする、content: メモの内容
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
//              constrained: usersテーブルに実在するID参照を強制する
//              cascadeOnDelete: ユーザーがアカウントを削除した場合、そのユーザーのメモも自動的に削除する
            $table->string('title')->nullable();
//              nullable: titleの入力がない場合でもnullとして続行する
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memos');
    }
};
