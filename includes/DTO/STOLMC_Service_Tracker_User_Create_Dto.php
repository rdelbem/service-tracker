<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_User_Create_Dto {
	public string $name;
	public string $email;
	public string $role;
	public ?string $username;
	public ?string $password;
	public ?string $phone;
	public ?string $cellphone;

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct( array $data ) {
		$name  = isset( $data['name'] ) ? trim( (string) $data['name'] ) : '';
		$email = isset( $data['email'] ) ? trim( (string) $data['email'] ) : '';

		if ( '' === $name || '' === $email ) {
			throw new Validation_Exception( 'Name and email are required' );
		}

		if ( ! is_email( $email ) ) {
			throw new Validation_Exception( 'Invalid email address' );
		}

		$this->name     = $name;
		$this->email    = $email;
		$this->role     = isset( $data['role'] ) && '' !== (string) $data['role'] ? (string) $data['role'] : 'customer';
		$this->username = isset( $data['username'] ) && '' !== (string) $data['username'] ? (string) $data['username'] : null;
		$this->password = isset( $data['password'] ) && '' !== (string) $data['password'] ? (string) $data['password'] : null;
		$this->phone    = isset( $data['phone'] ) && '' !== (string) $data['phone'] ? (string) $data['phone'] : null;
		$this->cellphone = isset( $data['cellphone'] ) && '' !== (string) $data['cellphone'] ? (string) $data['cellphone'] : null;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		$data = [
			'name'     => $this->name,
			'email'    => $this->email,
			'role'     => $this->role,
			'username' => $this->username,
			'password' => $this->password,
		];

		if ( null !== $this->phone ) {
			$data['phone'] = $this->phone;
		}

		if ( null !== $this->cellphone ) {
			$data['cellphone'] = $this->cellphone;
		}

		return $data;
	}
}
