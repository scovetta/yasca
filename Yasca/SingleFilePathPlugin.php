<?
declare(encoding='UTF-8');
namespace Yasca;

trait SingleFilePathPlugin {
	abstract public function getResultIterator($filepath);
}