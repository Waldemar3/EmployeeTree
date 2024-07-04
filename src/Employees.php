<?php namespace App;

class Employees extends Table
{
	private $regexp = [
        'name' => '/^[А-яA-z0-9 ]{3,32}$/',
        'email' => '/^\\S+@\\S+\\.\\S+$/',
        'phone_number' => '/^\\+?[1-9][0-9]{7,14}$/',
        'text' => '/^[А-яA-z0-9 ]{3,255}$/',
    ];

	public function readAll(){
		return json_encode($this->select('id, name, surname'));
	}

	public function read($id){
		return json_encode($this->where('id='.$id)->limit(1)->select('*'));
	}

	public function create(array $employee){
		if(empty($employee)){
            throw new \Exception("Тело запроса пустое");
        }

		return $this->insert([
			'name' => $this->validateName($employee['name']),
			'surname' => $this->validateName($employee['surname']),
			'job' => $this->validateJob($employee['job']),
			'email' => $this->validateEmail($employee['email']),
			'phone_number' => $this->validatePhoneNumber($employee['phone_number']),
			'notes' => $this->validateNotes($employee['notes']),
			'timestamp' => $this->timestamp,
		]);
	}

	public function change(array $employee, $id){
		if(empty($employee)){
            throw new \Exception("Тело запроса пустое");
        }

		return $this->where('id='.$id)->update([
			'name' => $this->validateName($employee['name']),
			'surname' => $this->validateName($employee['surname']),
			'job' => $this->validateJob($employee['job']),
			'email' => $this->validateEmail($employee['email']),
			'phone_number' => $this->validatePhoneNumber($employee['phone_number']),
			'notes' => $this->validateNotes($employee['notes']),
			'timestamp' => $this->timestamp,
		]);
	}

	public function remove($id){
		return $this->where('id='.$id)->delete();
	}

	public function findByNameAndSurname(string $name){
		$name = explode(" ", $name);
		return $this->where("name='".$this->validateName($name[0])."' and surname='".$this->validateName($name[1])."'")->select('*')[0];
	}

	private function validateName($name){
        if(!preg_match($this->regexp['name'], $name)){
            throw new \Exception("Имя или фамилия имеет неправильный формат");
        }

        return $name;
    }

    private function validateEmail($email){
        if(!preg_match($this->regexp['email'], $email)){
            throw new \Exception("Неверный формат Email");
        }
        return $email;
    }

    private function validatePhoneNumber($phoneNumber){
        if(!preg_match($this->regexp['phone_number'], $phoneNumber)){
            throw new \Exception("Неверный формат номера телефона");
        }
        return $phoneNumber;
    }

    private function validateNotes($notes){
        if(!preg_match($this->regexp['text'], $notes)){
            throw new \Exception("Неверный формат заметок");
        }
        return $notes;
    }

    private function validateJob($job){
        if(!preg_match($this->regexp['text'], $job)){
            throw new \Exception("Неверный формат должности");
        }
        return $job;
    }

	public function factory($faker, $count){
		$employees = [];
		$ids = [];

		for ($i = 0; $i < $count; $i++) {
			$name = $faker->firstName;
			$surname = $faker->lastName;

			$id = $this->insert([
				'name' => $name,
				'surname' => $surname,
				'job' => $faker->company,
				'email' => $faker->email,
				'phone_number' => $faker->phoneNumber,
				'notes' => $faker->text(20),
				'timestamp' => $this->timestamp,
			]);

			$ids[] = $id; 
			$employees[] = $name ." ". $surname;
		}

		return [$ids, $employees];
	}

    protected function migrate(): string {
	    return "
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			surname VARCHAR(255) NOT NULL,
			email VARCHAR(255) NOT NULL,
			phone_number VARCHAR(255) NOT NULL,
			job VARCHAR(255) NOT NULL,
			notes TEXT NOT NULL,
			timestamp INT UNSIGNED NOT NULL,

			PRIMARY KEY (id)
		";
	}
}